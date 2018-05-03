<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title>homedocs</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="templates/bootstrap-3.1.0/css/bootstrap.min.css" rel="stylesheet">
	<link href="templates/css/default.css" rel="stylesheet">
	<link href="templates/dropzone/dropzone.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
	<body>


<div class="container">

      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">homedocs</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="act active"><a id="show_dragdrop_upload" href="#">Upload!</a></li>
            <li class="act"><a href="#" id="show_search" class="dropdown-toggle" data-toggle="dropdown">Advanced search</a></li>
            <li>
      				<form id="simple_search" action="api/storage/search.php" method="post" class="navbar-form navbar-left" role="search">
      					<div class="form-group">
      						<input type="text" name="q" class="form-control" placeholder="search">
      					</div>
      					<button type="submit" class="btn btn-default">Fast Search</button>
      				</form>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a id="logout_link" href="api/user/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>


      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron section_container" id="drag_drop_container">
        <h1>New Document</h1>
        <p>Drag & Drop file/s into this area (or click & select) to create new document.</p>
        <form enctype="multipart/form-data" action="api/storage/upload.php" class="dropzone" id="dragdropUploadContainer">
          <div class="dz-message"></div>
          <div class="fallback">
            <input name="userfile" type="file"  multiple="" />
          </div>
        </form>
        <p>
          <a class="btn btn-lg btn-primary" href="#" id="select_file_to_upload_button">Select file/s &raquo;</a>
        </p>
      </div>

      <div class="jumbotron section_container" id="search_container">
        <h1>Search documents</h1>
        <p>Set filter conditions:</p>
        <form id="advanced_search" method="post" action="api/storage/search.php" role="search">
            <div class="form-group">
              <label for="q">search text</label>
              <input type="text" name="q" class="form-control" placeholder="words">
            </div>
            <div class="form-group">
              <label for="tags">tags</label>
              <input type="text" name="tags" class="form-control" placeholder="enter (optional) tags">
            </div>
            <div class="form-group">
              <label for="creation_date">Created</label>
              <select class="form-control" name="creation_date" id="creation_date">
                <option value="0">Anytime</option>
                <option value="1">Today</option>
                <option value="2">Yesterday</option>
                <option value="3">Last Week</option>
                <option value="4">Last Month</option>
                <option value="5">Last Year</option>
              </select>
            </div>
            <button type="submit" class="btn btn-default">Search!</button>
        </form>
      </div>

		<div id="results" class="section_container"></div>


    </div> <!-- /container -->

		<div id="file_details" class="modal fade" tabindex="-1">

      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Document details (<span id="total_files">0</span> file/s attached) - <a id="permalink" href="#">permalink</a></h4>
          </div>
          <div class="modal-body">
            <div class="row">
              <div id="document_file_details" class="col-xs-6 col-md-4">
                <img class="center-block" id="fd_mime_icon_url" src="#" alt="mime icon" />
                <p class="text-center" id="pag"><span id="prev_download" class="glyphicon glyphicon-chevron-left"></span> change file <span id="next_download" class="glyphicon glyphicon-chevron-right"></span></p>
                <p class="text-center" id="fd_filename"></p>
                <p class="text-center"><a id="fd_download_url" href="#">download file (<span id="fd_filesize"></span>)</a></p>
                <p class="text-center"><a id="fd_remove_file" href="#">remove file</a></p>
              </div>
              <div class="col-xs-12 col-md-8">
                <form class="form-horizontal" role="form" method="post" action="api/storage/update.php" id="update_file_details_form">
                  <input type="hidden" id="update_document_id" name="id" value="" />
                  <div class="form-group">
                    <label class="col-sm-2 control-label">Title</label>
                    <div class="col-sm-10">
                      <p><input type="text" name="title" class="form-control" id="fd_title" value="" placeholder="enter (optional) title"></p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label">Desc</label>
                    <div class="col-sm-10">
                      <p><textarea name="description" id="fd_description" class="form-control" rows="3" placeholder="enter (optional) description"></textarea></p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="inputPassword" class="col-sm-2 control-label">Tags</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" id="fd_tags" name="tags" value="" placeholder="enter (optional) tags">
                    </div>
                  </div>
              </form>
            </div>
          </div>
          <img class="center-block" src="#" id="fl_thumb_url" alt="file thumbnail" />
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="submit_update_form_button">Update</button>
            <button type="button" class="btn btn-danger" id="submit_delete_form_button" data-dismiss="modal">Delete</button>
          </div>
        </div>
      </div>
    </div>

		<script src="templates/jquery-2.1.0.min.js"></script>
		<script src="templates/bootstrap-3.1.0/js/bootstrap.min.js"></script>
    <script src="templates/dropzone/dropzone.min.js"></script>
		<script src="templates/js/default.js"></script>
	</body>
</html>