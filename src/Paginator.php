<?php declare(strict_types=1);

namespace HAE\Guestbook;

use InvalidArgumentException;
use mysqli;

class Paginator
{
    private const NUM_PLACEHOLDER = '(:num)';

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $totalItems;

    /**
     * @var int
     */
    private $totalPages;

    /**
     * @var string
     */
    private $urlPattern;

    /**
     * @var int
     */
    private $pagesToShow = 5;

    /**
     * @var string
     */
    private $previousText = 'Previous';

    /**
     * @var string
     */
    private $nextText = 'Next';

    /**
     * @var mysqli
     */
    private $link;

    /**
     * @var mixed
     */
    private $result;

    public function __construct()
    {
        $this->setLink(new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME));
        $this->setPerPage(ITEMS_PER_PAGE);
        $this->calcTotalItems();
        $this->fetchRoute();
        $this->setUrlPattern('/page/(:num)');
        $this->updatePagesCount();
        $this->fetchResult();
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     * @return Paginator
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return Paginator
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param int $total
     * @return Paginator
     */
    public function setTotalItems(int $total): self
    {
        $this->totalItems = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $pages
     * @return Paginator
     */
    public function setTotalPages(int $pages): self
    {
        $this->totalPages = $pages;
        return $this;
    }

    /**
     * @param string $urlPattern
     * @return Paginator
     */
    public function setUrlPattern($urlPattern): self
    {
        $this->urlPattern = $urlPattern;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPattern(): string
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->getUrlPattern());
    }

    public function getNextPage(): ?int
    {
        if ($this->getCurrentPage() < $this->getTotalPages()) {
            return $this->getCurrentPage() + 1;
        }
        return null;
    }

    public function getPrevPage(): ?int
    {
        if ($this->getCurrentPage() > 1) {
            return $this->getCurrentPage() - 1;
        }
        return null;
    }

    public function getNextUrl(): ?string
    {
        if (!$this->getNextPage()) {
            return null;
        }
        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl(): ?string
    {
        if (!$this->getPrevPage()) {
            return null;
        }
        return $this->getPageUrl($this->getPrevPage());
    }


    /**
     * @param int $max
     * @return Paginator
     */
    public function setMaxPagesToShow(int $max): self
    {
        if ($max < 3) {
            throw new InvalidArgumentException('pagesToShow cannot be less than 3.');
        }
        $this->setMaxPagesToShow($max);
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->pagesToShow;
    }

    /**
     * @return string
     */
    public function getPreviousText(): string
    {
        return $this->previousText;
    }

    /**
     * @param string $previousText
     * @return Paginator
     */
    public function setPreviousText(string $previousText): self
    {
        $this->previousText = $previousText;
        return $this;
    }

    /**
     * @return string
     */
    public function getNextText(): string
    {
        return $this->nextText;
    }

    /**
     * @param string $nextText
     * @return Paginator
     */
    public function setNextText(string $nextText): self
    {
        $this->nextText = $nextText;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return Paginator
     */
    public function setResult($result): self
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return mixed
     */
    public function getPages()
    {
        $pages = array();
        if ($this->getTotalPages() <= 1) {
            return array();
        }
        if ($this->getTotalPages() <= $this->getMaxPagesToShow()) {
            for ($i = 1; $i <= $this->getTotalPages(); $i++) {
                $pages[] = $this->createPage($i, $i == $this->getCurrentPage());
            }
        } else {
            // Determine the sliding range, centered around the current page.
            $numAdjacent = (int)floor(($this->getMaxPagesToShow() - 3) / 2);
            if ($this->getCurrentPage() + $numAdjacent > $this->getTotalPages()) {
                $slidingStart = $this->getTotalPages() - $this->getMaxPagesToShow() + 2;
            } else {
                $slidingStart = $this->getCurrentPage() - $numAdjacent;
            }
            if ($slidingStart < 2) $slidingStart = 2;
            $slidingEnd = $slidingStart + $this->getMaxPagesToShow() - 3;
            if ($slidingEnd >= $this->getTotalPages()) $slidingEnd = $this->getTotalPages() - 1;
            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->getCurrentPage() == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->getCurrentPage());
            }
            if ($slidingEnd < $this->getTotalPages() - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->getTotalPages(), $this->getCurrentPage() === $this->getTotalPages());
        }
        return $pages;
    }

    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return mixed
     */
    private function createPage($pageNum, $isCurrent = false)
    {
        return [
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        ];
    }

    /**
     * @return mixed
     */
    private function createPageEllipsis()
    {
        return [
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        ];
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function render()
    {
        if ($this->getTotalPages() <= 1) {
            return '';
        }

        $html = '<div class="paging row text-center">';
        if ($this->getPrevUrl()) {
            $html .= '<a href="' . htmlspecialchars($this->getPrevUrl()) . '"><i class="fa m-b-md">&laquo; ' . $this->getPreviousText() . '</i></a>';
        }

        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                $html .= '><a href="' . htmlspecialchars($page['url']) . '"><i class="fa m-b-md' . ($page['isCurrent'] ? ' active' : '') .'">' . htmlspecialchars($page['num']) . '</i></a>';
            } else {
                $html .= '<a><i class="fa m-b-md">' . htmlspecialchars($page['num']) . '</i></a>';
            }
        }

        if ($this->getNextUrl()) {
            $html .= '<a href="' . htmlspecialchars($this->getNextUrl()) . '"><i class="fa m-b-md">' . $this->getNextText() . ' &raquo;</i></a>';
        }

        $html .= '</div>';

        return $html;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    private function updatePagesCount(): void
    {
        $this->setTotalPages($this->getPerPage() === 0 ? 0 : (int)ceil($this->getTotalItems() / $this->getPerPage()));
    }

    /**
     * @return mysqli
     */
    private function getLink(): mysqli
    {
        return $this->link;
    }

    /**
     * @param mysqli $link
     * @return Paginator
     */
    private function setLink(mysqli $link): self
    {
        $this->link = $link;
        return $this;
    }

    private function calcTotalItems(): void
    {
        $result = $this->getLink()->query('SELECT COUNT(*) AS c FROM post');

        $items = $result->fetch_object()->c;

        $this->setTotalItems((int) $items);
    }

    private function fetchRoute(): void
    {
        list ($route, $page) = explode('/', substr($_SERVER['REQUEST_URI'], 1));

        $this->setCurrentPage((int) $page);

        if ($route !== 'page') {
            $this->setCurrentPage(1);
        }
    }

    private function fetchResult(): void
    {
        $start = ($this->getCurrentPage() - 1) * $this->getPerPage();

        if ($start > $this->getTotalItems()) {
            $start = 0;
        }

        $data = [];
        $query = sprintf(
            'SELECT * FROM post ORDER BY published DESC LIMIT %d, %d',
            $start,
            $this->getPerPage()
        );

        $result = $this->getLink()->query($query);

        while ($row = $result->fetch_object()) {
            $data[] = $row;
        }

        $this->setResult($data);
    }
}
