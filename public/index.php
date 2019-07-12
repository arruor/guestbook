<?php declare(strict_types=1);

use HAE\Guestbook\Paginator;

require dirname(__DIR__) . '/config/config.php';
require dirname(__DIR__) . '/vendor/autoload.php';

$paginator = new Paginator();

?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" href="apple-touch-icon.png">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="css/main.css">
    <script src="js/vendor/modernizr-2.8.3-respond-1.4.2.min.js"></script>
    <script src="js/vendor/fa.min.js"></script>
</head>
<body>
<div class="wrapper">
<!--[if lt IE 8]>
<p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->
<div class="container">
    <!-- Example row of columns -->
    <div class="row content">
        <div class="col-sm-3 heading">
            <div class="row text-center logo">
            <a href="/" class="">
                <img src="images/hae-logo.svg" class="center-block img-responsive" alt="HAE group logo" width="142px">
            </a>
            </div>
            <hr>
            <h3 class="text-center">Guestbook</h3>
            <p class="text-justify">Feel free to leave us a short message to tell us what you think to our services
                <br><a class="title m-b-md" role="button" data-toggle="modal" data-target="#modalPost">Post a message</a>
            </p>
            <p class="footer text-left title m-b-md" data-toggle="modal" data-target="#modal" >Admin Login</p>
        </div>
        <div class="col-md-9 entries">
            <div class="row">
                <?php if (!empty($paginator->getResult())): ?>
                <?php foreach ($paginator->getResult() as $post): ?>
                    <div class="col-md-6 entry">
                        <p class="post"><?php echo substr($post->content, 0, 140) . '...'; ?></p>
                        <span class="author"><?php echo $post->author; ?></span><br>
                        <span class="date">
                            <?php $date = DateTime::createFromFormat('Y-m-d H:i:s', $post->published); echo $date->format('jS M, Y') . ' at ' . $date->format('h:ia'); ?>
                        </span>
                        <span class="actions">
                            <i data-id="<?php echo $post->id;?>" class="fa fa-pencil"></i>
                            <i data-id="<?php echo $post->id;?>" class="fa fa-trash"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="paging row text-center">
                <a><i class="fa m-b-md">&lt;</i></a>
                <a><i class="fa m-b-md">1</i></a>
                <a><i class="fa m-b-md active">2</i></a>
                <a><i class="fa m-b-md">3</i></a>
                <a><i class="fa m-b-md">&gt;</i></a>
            </div>
        </div>
    </div>
</div>
</div>

<div id="modal" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">Login</h1>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" method="POST" action="/login">
                    <div class="form-group">
                        <label for="email" class="control-label">E-Mail Address</label>
                        <div>
                            <input id="email" type="email" class="form-control" name="email" value="" autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="control-label">Password</label>
                        <div>
                            <input id="password" type="password" class="form-control" name="password">
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember"> Remember Me
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                Login
                            </button>
                            <a class="btn btn-link" href="/password/reset">
                                Forgot Your Password?
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="modalPost" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title">Post a message</h1>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" role="form" method="POST" action="">
                    <div class="form-group">
                        <label for="author" class="control-label">Name</label>
                        <div>
                            <input id="author" type="text" class="form-control" name="author" value="" autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content" class="control-label">Message</label>
                        <div>
                            <textarea id="content" name="content" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="js/vendor/jquery-1.11.2.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.11.2.min.js"><\/script>')</script>
<script src="js/vendor/bootstrap.min.js"></script>
<script src="js/plugins.js"></script>
<script src="js/main.js"></script>
</body>
</html>
