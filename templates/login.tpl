<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>homedocs - log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="templates/bootstrap-3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="templates/css/login.css">

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
  </head>

  <body>

    <div id="header">
        <img src="templates/img/logo.png" alt="homedocs main logo" />
    </div>

    <div id="login_container">
        <form role="form" method="post" action="api/user/login.php" id="login_form">
            <h2>Log in</h2>
            <div class="form-group">
                <label for="email_login">Email</label>
                <input type="email" name="email" id="email_login" class="form-control" maxlength="128" required>
            </div>
            <div class="form-group">
                <label for="password_login">Password</label>
                <input type="password" name="password" id="password_login" class="form-control" required>
            </div>
            <div class="form-group">
                <input class="btn btn-primary" type="submit" value="Log in" />
                <a id="forgot_password_link" href="#">Forgot your password?</a>
            </div>
            <div class="form-group">
                <p class="quiet clear">New here ? <a id="create_account_link" href="#"><strong>Create an Account.</strong></a></p>
            </div>
        </form>
        <?php
            if (file_exists("install.php"))
            {
                ?>
                    <div class="alert alert-warning alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <p><strong>WARNING: </strong> installer file (install.php) found</p><p><strong>DELETE after <a href="install.php">install</a></strong></p>
                    </div>                
                <?php
            }
        ?>
    </div>

    <div id="new_account_container">
        <form role="form" method="post" action="api/user/add.php" id="add_account_form">
            <h2>New account</h2>
            <div class="form-group">
                <label for="email_new">Email</label>
                <input type="email" name="email" id="email_new" class="form-control" maxlength="128" required>
            </div>
            <div class="form-group">
                <label for="password_new">Password</label>
                <input type="password" name="password" id="password_new" class="form-control" required>
            </div>
            <div class="form-group">
                <input class="btn btn-primary" type="submit" value="Add" />
                <input class="btn btn-info back_to_login_button" type="reset" value="Go back" />
            </div>
        </form>
    </div>

    <div id="recover_account_container">
        <form role="form" method="post" action="api/user/recover_account.php" id="recover_account_form">
            <h2>Forgot your password? </h2>
            <p>Submit your email address and we’ll send you a link to reset your password.</p>
            <div class="form-group">
                <label for="email_recover">Email</label>
                <input type="email" name="email" id="email_recover" class="form-control" maxlength="128" required>
            </div>
            <div class="form-group">
                <input class="btn btn-primary" type="submit" value="Submit" />
                <input class="btn btn-info back_to_login_button" type="reset" value="Go back" />
            </div>
        </form>
    </div>

    <script src="templates/jquery-2.1.0.min.js"></script>
    <script src="templates/bootstrap-3.1.0/js/bootstrap.min.js"></script>
    <script src="templates/js/login.js"></script>

  </body>
</html>
