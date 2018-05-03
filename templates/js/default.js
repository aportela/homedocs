/*
	credits: http://stackoverflow.com/a/14919494
*/
function humanFileSize(bytes, si) {
	var thresh = si ? 1000 : 1024;
	if (bytes < thresh) return bytes + ' B';
	var units = si ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'] : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
	var u = -1;
	do {
		bytes /= thresh;
		++u;
	} while (bytes >= thresh);
	return bytes.toFixed(1) + ' ' + units[u];
};

function get_icon_url(filename) {
	var extension = filename.split('.').pop();;
	switch (extension) {
		case "xls":
			imagePath = "templates/img/mime_excel.png";
			break;
		case "txt":
			imagePath = "templates/img/mime_text.png";
			break;
		case "doc":
			imagePath = "templates/img/mime_word.png";
			break;
		case "ppt":
			imagePath = "templates/img/mime_ppt.png";
			break;
		case "jpg":
		case "jpeg":
		case "png":
		case "gif":
		case "bmp":
		case "tiff":
			imagePath = "templates/img/mime_image.png";
			break;
		case "pdf":
			imagePath = "templates/img/mime_pdf.png";
			break;
		default:
			imagePath = "templates/img/mime_default.png";
			break;
	}
	return (imagePath);
}

function draw_results(data) {
	if (data != null && data.documents != null && data.documents.length > 0) {
		var max_cols = 3;
		var col = 0;
		var HTML = '';
		for (var i = 0; i < data.documents.length; i++) {
			if (col == 0) {
				HTML += '<div class="row">';
			}
			var imagePath = data.documents[i].files.length == 1 ? get_icon_url(data.documents[i].title) : "templates/img/mime_folder.png";
			HTML += '<div class="col-md-4">' +
				'	<div class="document">' +
				'		<h5 class="title center-block" title="' + data.documents[i].title + '">' + data.documents[i].title + '</h5>' +
				'		<h6 class="creation_date">Created on ' + data.documents[i].created + '</h6>' +
				'		<img src="templates/img/' + (data.documents[i].total_files == 1 ? "mime_default.png" : "mime_folder.png") + '" alt="document icon">' +
				'		<a class="document_details_link" href="#" title="click to open document details" data-id="' + data.documents[i].id + '">details</a>' +
				'	</div>' +
				'</div>';

			col++;
			if (col == max_cols) {
				HTML += '</div>';
				col = 0;
			}
		}
		$("div#results").html(HTML);
		$("div#results").show();
	}
	else {
		$("div#results").html('<div class="alert alert-warning">No documents found</div>');
		$("div#results").show();
	}
	$("div.jumbotron").hide();
	$("form.dropzone").hide();
	$("li.act").removeClass("active");
}

$("form#advanced_search").submit(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$("li.act").removeClass("active");
	$.post($(this).attr("action"), $(this).serialize(), function (data) {
		draw_results(data);
	});
});

$("form#simple_search").submit(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$.post($(this).attr("action"), $(this).serialize(), function (data) {
		draw_results(data);
	});
});

$("a#toggle_upload").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$("a.document_details_link").tooltip('hide')
	$("form.dropzone").toggle();
});

var loadedDocument = null;
var documentFileIdx = 0;

function refreshDocumentFile() {
	if (loadedDocument.total_files > 0) {
		var thumb_url = loadedDocument.files[documentFileIdx].thumbnail_url;
		var filename = loadedDocument.files[documentFileIdx].name;
		var filesize = loadedDocument.files[documentFileIdx].size;
		var filename = loadedDocument.files[documentFileIdx].name;
		var img = get_icon_url(filename);
		var download_url = "api/storage/download.php?id=" + loadedDocument.files[documentFileIdx].id;
		var delete_url = "api/storage/delete_file.php?id=" + loadedDocument.files[documentFileIdx].id + "&hash=" + loadedDocument.files[documentFileIdx].hash;
		$("p#fd_filename").text(filename);
		$("p#fd_filename").attr("title", filename);
		$("span#fd_filesize").text(humanFileSize(filesize));
		$("div#document_file_details p, div#document_file_detailsimg").show();
	} else {
		$("div#document_file_details p, div#document_file_detailsimg").hide();
	}
	if (thumb_url != null) {
		$("img#fl_thumb_url").attr("src", thumb_url);
		$("img#fl_thumb_url").show();
	} else {
		$("img#fl_thumb_url").attr("src", "#");
		$("img#fl_thumb_url").hide();
	}
	$("img#fd_mime_icon_url").attr("src", img);
	$("a#fd_download_url").attr("href", download_url);
	$("a#fd_remove_file").attr("href", delete_url);
}

function showDetails(id) {
	$.get("api/storage/get.php?id=" + id, function (data) {
		loadedDocument = data.document;
		documentFileIdx = 0;
		var title = data.document.title;
		var description = data.document.description;
		var tags = data.document.tags.join(", ");
		$("span#total_files").text(data.document.total_files)
		$("input#update_document_id").val(id);
		if (title != null && title.length > 0) {
			$("input#fd_title").val(title);
		}
		else {
			$("input#fd_title").val('');
		}
		if (description != null && description.length > 0) {
			$("textarea#fd_description").val(description);
		}
		else {
			$("textarea#fd_description").val('');
		}
		if (tags.length > 0) {
			$("input#fd_tags").val(tags);
		}
		else {
			$("input#fd_tags").val('');
		}
		if (data.document.total_files > 1) {
			$("p#pag").show();
		}
		else {
			$("p#pag").hide();
		}
		refreshDocumentFile();
		$("a#permalink").attr("href", window.location.href.replace(window.location.search, "") + '?permalink=' + id);
		$('div#file_details').modal('show')
	});
}

$("span#prev_download").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (loadedDocument.total_files > 1) {
		if (documentFileIdx > 0) {
			documentFileIdx--;
			refreshDocumentFile();
		}
	}
});

$("span#next_download").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (loadedDocument.total_files > 1) {
		if (documentFileIdx < (loadedDocument.total_files - 1)) {
			documentFileIdx++;
			refreshDocumentFile();
		}
	}
});

$("div#results").on("click", "a.document_details_link", function (e) {
	e.preventDefault();
	e.stopPropagation();
	showDetails($(this).data("id"));
});

var reOpenDetails = false;
var openDocumentId = null;
$('div#file_details').on('hidden.bs.modal', function (e) {
	if (reOpenDetails && openDocumentId !== null) {
		showDetails(openDocumentId);
	}
	openDocumentId = null;
	reOpenDetails = false;
});

$("div#file_details").on("click", "a#fd_remove_file", function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (confirm('Are you sure you want to delete this file ?')) {
		var documentId = $("input#update_document_id").val();
		var url = $("a#fd_remove_file").attr("href");
		$.get(url, function (response) {
			$('div#file_details').modal('hide');
			reOpenDetails = true;
			openDocumentId = documentId;
		});
	}
});

$("button#submit_update_form_button").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	var data = $("form#update_file_details_form").serialize();
	$.post($("form#update_file_details_form").attr("action"), data, function (response) {
		$('div#file_details').modal('hide');
		$("form#simple_search").submit();
	});
});

$("button#submit_delete_form_button").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (confirm("Are you sure you want to delete this document ?")) {
		var documentId = $("input#update_document_id").val();
		$.post("api/storage/delete_document.php", "id=" + documentId, function (response) {
			$('div#file_details').modal('hide');
			var result = $('a[data-id="' + documentId + '"]');
			if (result.length === 1) {
				$(result).closest("div.document").remove();
			}
		});
	};
});

$("a#logout_link").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$.get($(this).attr("href"), function (data) {
		window.location.href = window.location.href;
	});
});

$("a#show_dragdrop_upload").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$("div.dz-preview").remove();
	$("div.section_container").hide();
	$("div#drag_drop_container").show();
	$("form#dragdropUploadContainer").show();
	$("li.act").removeClass("active");
	$(this).parent("li").addClass("active");
});

$("a#show_search").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$("div.section_container").hide();
	$("div#search_container").show();
	$("li.act").removeClass("active");
	$(this).parent("li").addClass("active");
});

$("a#select_file_to_upload_button").click(function (e) {
	e.preventDefault();
	e.stopPropagation();
	$("form#dragdropUploadContainer").click();
});

Dropzone.options.dragdropUploadContainer = {
	paramName: "userfile", // The name that will be used to transfer the file
	maxFilesize: 16, // MB
	addRemoveLinks: true,
	parallelUploads: 24,
	uploadMultiple: true,
	maxFiles: 24,
	//autoProcessQueue: false,
	acceptedFiles: ".pdf,.jpg,.jpeg,.bmp,.tiff,.png,.gif,.txt,.doc,.docx,.html,.xml,.rtf,.xls,.csv,.ppt",
	init: function () {
		this.on("drop", function (files) {
			if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
				this.removeAllFiles();
			}
		});
		this.on("successmultiple", function (files, serverResponse) {
			if (serverResponse.success) {
				showDetails(serverResponse.document.id);
			}
		})
	}
};

function loadTags() {
	$.get("api/user/get_tags.php", function (data) {
		if (data.success) {
			var HTML = "";
			for (var i = 0; i < data.tags.length; i++) {
				HTML += '<option value="' + data.tags[i] + '">' + data.tags[i] + '</option>';
			}
			$("select#select_tag_filter").html(HTML);
		}
	});
}

loadTags();

var url = new URL(window.location.href);
var permalink = url.searchParams.get("permalink");
if (permalink) {
	showDetails(permalink);
}
