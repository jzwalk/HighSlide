<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$fileurl = HighSlide_Plugin::filedata()->url;

//异步加载上传列表
if(isset($_GET["action"])&&$_GET["action"]=="loadlist") {
	$lists = HighSlide_Plugin::filelist();
	if ($lists) {
		foreach ($lists as $list) {
?>
		<li id="queue-<?php echo $list['id']; ?>" data-name="<?php echo $list['name']; ?>">
			<?php if(false===strpos($list['name'],'thumb_')): ?>
			<img id="uploadimg-queue-<?php echo $list['id']; ?>" class="preview" style="max-width:442px;" src="<?php echo $fileurl.$list['name']; ?>" alt="<?php echo $list['name']; ?>" />
			<div class="info">
				<?php echo $list['size']; ?> Kb
				<a class="crop" href="###" title="<?php _e('截取缩略图'); ?>"><i class="mime-application"></i></a>
				<a class="delete" href="###" title="<?php _e('删除原图(同时删除其缩略图)'); ?>"><i class="i-delete"></i></a>
				<input type="text" id="image-url" style="padding:0;width:355px;float:right;overflow:hidden;" value="<?php echo $fileurl.$list['name']; ?>" readonly />
			</div>
			<input type="hidden" name="x1" value="" id="x1" />
			<input type="hidden" name="y1" value="" id="y1" />
			<input type="hidden" name="x2" value="" id="x2" />
			<input type="hidden" name="y2" value="" id="y2" />
			<input type="hidden" name="w" value="" id="w" />
			<input type="hidden" name="h" value="" id="h" />
			<?php else: ?>
			<img class="preview" style="max-width:250px;overflow:hidden;" src="<?php echo $fileurl.$list['name']; ?>" alt="<?php echo $list['name']; ?>"/>
			<textarea id="thumb-url" style="padding:0;float:right;word-break:break-all;" readonly ><?php echo $fileurl.$list['name']; ?></textarea>
			<div class="info">
				<?php echo $list['size']; ?> Kb
				<a class="addto" href="#tab-forms" title="<?php _e('添加图片'); ?>"><i class="i-exlink"></i></a>
				<a class="delete" href="###" title="<?php _e('删除缩略图'); ?>"><i class="i-delete"></i></a>
			</div>
			<?php endif; ?>
		</li>
<?php
		}
	}
} else {
include'common.php';
include'header.php';
include'menu.php';

$settings = $options->plugin('HighSlide');

//获取相册数据
$datas1 = $db->fetchAll($db->select('sort')->from('table.gallery')->order('order',Typecho_Db::SORT_ASC));
foreach ($datas1 as $data1) {
	$sorts[] = $data1['sort'];
}
if(!empty($sorts)) {
	$groups = array_unique($sorts);
	sort($groups);
	$group1 = array_shift($groups);
	$galleries1 = $db->fetchAll($db->select()->from('table.gallery')->where('sort=?',$group1)->order('order',Typecho_Db::SORT_ASC));
}

//获取缩略比例
$ratio = ($settings->thumbfix=='fixedratio')?$settings->fixedratio:'false';
?>
<div class="main">
	<div class="body container">
		<?php include 'page-title.php'; ?>
		<div class="colgroup typecho-page-main manage-galleries">

			<div class="col-mb-12 typecho-list">

				<div class="clearfix">
					<ul class="typecho-option-tabs right">
						<li<?php if(!isset($request->tab)||'images'==$request->get('tab')): ?> class="active w-50"<?php else: ?> class="w-50"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php'); ?>"><?php _e('图片编辑'); ?></a></li>
						<li<?php if('settings'==$request->get('tab')): ?> class="active w-50"<?php else: ?> class="w-50"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php&tab=settings'); ?>" id="tab-files-btn"><?php _e('相册设置'); ?></a></li>
					</ul>
					<ul class="typecho-option-tabs">
						<?php if(!empty($sorts)): ?>
						<li<?php if(!isset($request->group)||$group1==$request->get('group')): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php'); ?>"><span class="balloon"><?php echo count($galleries1); ?></span> <?php echo _e('相册组-'.$group1); ?></a></li>
						<?php foreach ($groups as $group):
						$galleries = $db->fetchAll($db->select('gid')->from('table.gallery')->where('sort=?',$group)); ?>
						<li<?php if($group==$request->get('group')): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php&group='.$group); ?>"><span class="balloon"><?php echo count($galleries); ?></span> <?php echo _e('相册组-'.$group); ?></a></li>
						<?php endforeach; else: ?>
						<li class="current"><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php'); ?>"><?php _e('相册组'); ?></a></li>
						<?php endif; ?>
						<li><a href="http://www.jzwalk.com/archives/net/highslide-for-typecho/" title="查看页面相册使用帮助" target="_blank"><?php _e('帮助'); ?></a></li>
					</ul>
				</div>

				<div class="col-mb-12 col-tb-7" role="main">
					<form method="post" name="manage_galleries" class="operate-form">
					<div class="typecho-list-operate clearfix">
						<div class="operate">
							<input type="checkbox" class="typecho-table-select-all" />
							<div class="btn-group btn-drop">
								<button class="dropdown-toggle btn-s" type="button" href=""><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
								<ul class="dropdown-menu">
									<li><a lang="<?php _e('你确认要移除这些图片吗?'); ?>" href="<?php $options->index('/action/gallery-edit?do=delete'); ?>"><?php _e('移除'); ?></a></li>
								</ul>
							</div>
						</div>
					</div>

					<div class="typecho-table-wrap">
						<table class="typecho-list-table">
							<colgroup>
								<col width="20"/>
								<col width="20%"/>
								<col width="25%"/>
								<col width="55%"/>
							</colgroup>
							<thead>
								<tr>
									<th> </th>
									<th><?php _e('缩略图'); ?></th>
									<th><?php _e('图片名称'); ?></th>
									<th><?php _e('图片描述'); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if(!empty($sorts)):
									if(!isset($request->group)||$group1==$request->get('group')) {
									foreach ($galleries1 as $gallery1): ?>
									<tr id="gid-<?php echo $gallery1['gid']; ?>">
										<td><input type="checkbox" value="<?php echo $gallery1['gid']; ?>" name="gid[]"/></td>
										<td><a href="<?php echo $gallery1['image']; ?>" title="<?php _e('点击查看原图'); ?>" target="_blank"><img style="max-width:100px;" src="<?php echo $gallery1['thumb']; ?>" alt="<?php echo $gallery1['name']; ?>"/></a></td>
										<td><a class="balloon-button" href="<?php echo $request->makeUriByRequest('gid='.$gallery1['gid']); ?>" title="<?php _e('点击修改图片'); ?>"><?php echo $gallery1['name']; ?></a>
										<td><?php echo $gallery1['description']; ?></td>
									</tr>
									<?php endforeach;} else {
									foreach ($groups as $group) {
									$galleries = $db->fetchAll($db->select()->from('table.gallery')->where('sort=?',$group)->order('order',Typecho_Db::SORT_ASC));
										if($group==$request->get('group')) {
										foreach ($galleries as $gallery): ?>
									<tr id="gid-<?php echo $gallery['gid']; ?>">
										<td><input type="checkbox" value="<?php echo $gallery['gid']; ?>" name="gid[]"/></td>
										<td><a href="<?php echo $gallery['image']; ?>" title="<?php _e('点击查看原图'); ?>" target="_blank"><img style="max-width:100px;" src="<?php echo $gallery['thumb']; ?>" alt="<?php echo $gallery['name']; ?>"/></a></td>
										<td><a class="balloon-button" href="<?php echo $request->makeUriByRequest('gid='.$gallery['gid']); ?>" title="<?php _e('点击修改图片'); ?>"><?php echo $gallery['name']; ?></a>
										<td><?php echo $gallery['description']; ?></td>
									</tr>
									<?php endforeach;}}}
								else: ?>
								<tr>
									<td colspan="4"><h6 class="typecho-list-table-title"><?php _e('没有任何图片'); ?></h6></td>
								</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>

					</form>
				</div>

				<div class="col-mb-12 col-tb-5" role="complementary">
				<?php if(!isset($request->tab)||'images'==$request->get('tab')): ?>
					<link rel="stylesheet" type="text/css" media="all" href="<?php $options->pluginUrl('HighSlide/css/imgareaselect-animated.css'); ?>" />
					<div id="tab-files" class="tab-content">
						<div id="upload-panel" class="p">
							<div class="upload-area">
								<div style="position:relative;width:340px;margin:0 auto;">
									<form enctype="multipart/form-data" action="<?php $options->index('/action/gallery-edit?do=upload'); ?>" method="post">
										<input type="text" name="textfield" id="textfield" style="padding:0;height:22px;border:1px solid #cdcdcd;width:180px;"/>
										<input type="button" value="<?php _e('浏览...'); ?>" id="explore" style="background-color:#E9E9E6;color:#666;border:1px solid #D9D9D6;height:24px;width:70px;"/>
										<input type="file" name="file" id="file" size="28" onchange="document.getElementById('textfield').value=this.value" style="position:absolute;top:0;right:80px;height:24px;filter:alpha(opacity:0);opacity:0;width:260px"/>
										<input type="submit" value="<?php _e('上传'); ?>" id="upload" style="background-color:#E9E9E6;color:#666;border:1px solid #D9D9D6;height:24px;width:70px;"/>
									</form>
								</div>
							</div>
							<div style="margin-left:10px;"><strong><?php _e('如何将上传后的图片录入相册:'); ?></strong><br/><?php _e('1. 在图片上拖动鼠标, 点击左下角图标截取缩略图'); ?><br/><?php _e('2. 复制图片地址到下方表单, 填写各项后点击添加'); ?></div>
							<ul id="file-list"></ul>
						</div>
					</div>

					<div id="tab-forms"><?php HighSlide_Plugin::form()->render(); ?></div>
				<?php else: ?>
					<div id="tab-settings" class="typecho-content-panel">
						<?php HighSlide_Plugin::form('sync','2')->render(); ?>
					</div><!-- end #tab-advance -->
				<?php endif; ?>

			</div>

		</div>
	</div>
</div>

<?php
include'copyright.php';
include'common-js.php';
include'form-js.php';
?>

<script src="<?php $options->adminUrl('js/filedrop.js?v='.$suffixVersion); ?>"></script>
<script src="<?php $options->pluginUrl('HighSlide/js/imgareaselect.js'); ?>"></script>

<script type="text/javascript">
$(document).ready(function() {
	var table = $('.typecho-list-table').tableDnD({
		onDrop:function() {
			var ids = [];
			$('input[type=checkbox]',table).each(function() {
				ids.push($(this).val());
			});
			$.post('<?php $options->index('/action/gallery-edit?do=sort'); ?>',
				$.param({gid:ids}));
			$('tr',table).each(function (i) {
				if (i%2) {
					$(this).addClass('even');
				} else {
					$(this).removeClass('even');
				}
			});
		}
	});
	if (table.length>0) {
		table.tableSelectable({
			checkEl:'input[type=checkbox]',
			rowEl:'tr',
			selectAllEl:'.typecho-table-select-all',
			actionEl:'.dropdown-menu a'
		});
	} else {
		$('.typecho-list-notable').tableSelectable({
			checkEl:'input[type=checkbox]',
			rowEl:'li',
			selectAllEl :'.typecho-table-select-all',
			actionEl:'.dropdown-menu a'
			});
		}
		$('.btn-drop').dropdownMenu({
			btnEl:'.dropdown-toggle',
			menuEl:'.dropdown-menu'
		});
		$('.dropdown-menu button.merge').click(function() {
			var btn = $(this);
			btn.parents('form').attr('action',btn.attr('rel')).submit();
		});
	<?php if (isset($request->lid)): ?>
		$('.typecho-mini-panel').effect('highlight','#AACB36');
	<?php endif; ?>

	$("#upload").click(function() {
		if ($("#file").val()=="") {
		alert("请点击浏览选择图片");
		return false;
		}
	});
	$(':file').change(function() {
		var file = this.files[0];
		name = file.name;
		size = file.size;
		type = file.type;
		if(!/.(gif|jpg|jpeg|png|tiff|bmp)$/.test(file.type)) {
			alert('<?php _e('不是支持的图片类型'); ?>');
			$(this).val('');
		}
		else if(file.size><?php
		$val = function_exists('ini_get')?trim(ini_get('upload_max_filesize')):0;
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case'g':
				$val*=1024;
			case'm':
				$val*=1024;
			case'k':
				$val*=1024;
		}
		echo ceil($val/(1024*1024));
		?>*1024*1024) {
			alert('<?php $val = function_exists('ini_get')?trim(ini_get('upload_max_filesize')):0;
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
				$val*=1024;
			case 'm':
				$val*=1024;
			case 'k':
				$val*=1024;
		}
		$val = number_format(ceil($val/(1024*1024)));
		_e('图片大小不能超过%s',"{$val}Mb"); ?>');
			$(this).val('');
		}
	});

	var list = $('#file-list');
	$.ajax({
		type:'post',
		url:location.href+'&action=loadlist',
		beforeSend: function() {
			list.html('<li class="loading">列表加载中...</li>');
		},
		error:function() {
			list.text('列表加载失败, 请刷新页面重试');
		},
		success:function(content) {
			list.html(content)
			.find('li').each(function() {
				iasEffectEvent(this);
				imageAddtoEvent(this);
				imageDeleteEvent(this);
				thumbCropEvent(this);
				autoSelectEvent(this);
			});
		}
	});

	function fileUploadStart(file,id) {
		$('<li id="'+id+'" class="loading">'+file+'</li>').prependTo(list);
	}

	function fileUploadComplete(id,data) {
		var li = $('#'+id).removeClass('loading').data('name',data.name)
			.html('<input type="hidden" name="imgname" value="'+data.name+'" />'
				+'<img id="uploadimg-'+id+'" class="preview" src="<?php echo $fileurl; ?>'+data.name+'" alt="'+data.title+'" style="max-width:442px;"/><div class="info">'+data.bytes
				+' <a class="crop" href="###" title="<?php _e('截取缩略图'); ?>"><i class="mime-application"></i></a>'
				+' <a class="delete" href="###" title="<?php _e('删除原图(同时删除其缩略图)'); ?>"><i class="i-delete"></i></a></div>'
				+' <input type="text" id="image-url" style="padding:0;width:355px;float:right;overflow:hidden;position:relative;bottom:19px;" value="<?php echo $fileurl; ?>'+data.name+'" readonly />'
				+'<input type="hidden" name="x1" value="" id="x1" />'
				+'<input type="hidden" name="y1" value="" id="y1" />'
				+'<input type="hidden" name="x2" value="" id="x2" />'
				+'<input type="hidden" name="y2" value="" id="y2" />'
				+'<input type="hidden" name="w" value="" id="w" />'
				+'<input type="hidden" name="h" value="" id="h" />')
			.effect('highlight',1000);
		$('#image-0-1').val('<?php echo $fileurl; ?>'+data.name+'');
		iasEffectEvent(li);
		imageDeleteEvent(li);
		thumbCropEvent(li);
		autoSelectEvent(li);
	}

	$('#upload-panel').filedrop({
		url:'<?php $options->index('/action/gallery-edit?do=upload'); ?>',
		allowedfileextensions:[".gif",".jpg",".jpeg",".png",".tiff",".bmp",".GIF",".JPG",".JPEG",".PNG",".TIFF",".BMP"],
		maxfilesize:<?php
		$val = function_exists('ini_get')?trim(ini_get('upload_max_filesize')):0;
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case'g':
				$val*=1024;
			case'm':
				$val*=1024;
			case'k':
				$val*=1024;
		}
		echo ceil($val/(1024*1024));
		?>,
		queuefiles:5,
		error: function(err,file) {
			switch(err) {
				case 'BrowserNotSupported':
					alert('<?php _e('浏览器不支持拖拽上传'); ?>');
					break;
				case 'TooManyFiles':
					alert('<?php _e('一次上传的图片不能多于%d个',25); ?>');
					break;
				case 'FileTooLarge':
					alert('<?php $val = function_exists('ini_get')?trim(ini_get('upload_max_filesize')):0;
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
				$val*=1024;
			case 'm':
				$val*=1024;
			case 'k':
				$val*=1024;
		}
		$val = number_format(ceil($val/(1024*1024)));
		_e('图片大小不能超过%s',"{$val}Mb"); ?>');
					break;
				case 'FileTypeNotAllowed':
					break;
				case 'FileExtensionNotAllowed':
					alert('<?php _e('文件 %s 不是支持的图片类型'); ?>'.replace('%s',file.name));
					break;
				default:
					break;
			}
		},
		dragOver:function() {
			$(this).addClass('drag');
		},
		dragLeave:function() {
			$(this).removeClass('drag');
		},
		drop:function() {
			$(this).removeClass('drag');
		},
		uploadStarted:function (i,file,len) {
			fileUploadStart(file.name,'drag-'+i);
		},
		uploadFinished:function (i,file,response) {
			fileUploadComplete('drag-'+i,response[0],response[1]);
		}
	});

	function imageAddtoEvent(el) {
		$('.addto',el).click(function () {
			var pli = $(this).parents('li'),
				name = pli.data('name'),
				parent = name.substr(6);
				$('#image-0-1').val('<?php echo $fileurl; ?>'+parent+'');
				$('#thumb-0-2').val('<?php echo $fileurl; ?>'+name+'');
			return false;
		});
	}

	function imageDeleteEvent(el) {
		$('.delete',el).click(function () {
			var pli = $(this).parents('li'),
				name = pli.data('name'),
				id = pli.attr('id');
			if (confirm('<?php _e('确认删除图片 %s 吗?'); ?>'.replace('%s',name))) {
				$.post('<?php $options->index('/action/gallery-edit'); ?>',
				{'do':'remove','imgname':name},
				function() {
					$('#uploadimg-'+id+'').imgAreaSelect({remove:true});
					$(el).fadeOut(function() {
						$(this).remove();
					});
					$('li[data-name="thumb_'+name+'"]').fadeOut(function() {
						$(this).remove();
					});
				});
			}
			return false;
		});
	}

	function thumbCropEvent(el) {
		$('.crop',el).click(function() {
			var pli = $(this).parents('li'),
				name = pli.data('name'),
				li = $('li[data-name="thumb_'+name+'"]'),
				x1 = $('#x1',el).val(),
				y1 = $('#y1',el).val(),
				x2 = $('#x2',el).val(),
				y2 = $('#y2',el).val(),
				w = $('#w',el).val(),
				h = $('#h',el).val();
			if(x1==""||y1==""||x2==""||y2==""||w==""||h=="") {
				alert("请先拖选图片区域");
				return false;
			}
			if (li.length==0) {
				$('<li data-name="thumb_'+name+'" class="loading"></li>').appendTo(pli);
			} else {
				li.empty().addClass('loading');
			}
			$('img[id^="uploadimg-"]').imgAreaSelect({hide:true});
			$.post('<?php $options->index('/action/gallery-edit'); ?>',
				{'do':'crop','imgname':name,'x1':x1,'y1':y1,'w':w,'h':h},
				function(data) {
					var li = $('li[data-name="thumb_'+name+'"]').removeClass('loading').html('<img class="preview" style="max-width:250px;float:left;overflow:hidden;" src="<?php echo $fileurl.'thumb_'; ?>'+name+'?u='+Math.floor(Math.random()*100)+'" alt="thumb_'+name+'" />'
					+'<textarea id="thumb-url" style="padding:0;float:right;word-break:break-all;" readonly ><?php echo $fileurl.'thumb_'; ?>'+name+'</textarea><div class="info" style="clear:both;">'+data.bytes
					+' <a class="addto" href="#tab-forms" title="<?php _e('添加图片'); ?>"><i class="i-exlink"></i></a>'
					+' <a class="delete" href="###" title="<?php _e('删除缩略图'); ?>"><i class="i-delete"></i></a></div>')
					.effect('highlight',1000);
				$('#image-0-1').val('<?php echo $fileurl; ?>'+name+'');
				$('#thumb-0-2').val('<?php echo $fileurl.'thumb_'; ?>'+name+'');
				imageAddtoEvent(li);
				imageDeleteEvent(li);
				autoSelectEvent(li);
				});
			return false;
		});
	}

	function iasEffectEvent(el) {
		var id = $(el).attr('id');
		$('#uploadimg-'+id+'',el).imgAreaSelect({
			handles:true,
			instance:true,
			classPrefix:'ias-'+id+' ias',
			aspectRatio:'<?php echo $ratio; ?>',
			onSelectEnd:function(img,selection) {
				$('#x1',el).val(selection.x1);
				$('#y1',el).val(selection.y1);
				$('#x2',el).val(selection.x2);
				$('#y2',el).val(selection.y2);
				$('#w',el).val(selection.width);
				$('#h',el).val(selection.height);
			}
		});
	}

	function autoSelectEvent(el) {
		$('#image-url,#thumb-url',el).click(function() {
			$(this).select();
		});
	}

	var local = $('#typecho-option-item-local-11'),
		qiniu = $('#typecho-option-item-qiniubucket-12,#typecho-option-item-qiniudomain-13,#typecho-option-item-qiniuaccesskey-14,#typecho-option-item-qiniusecretkey-15,#typecho-option-item-qiniuprefix-16'),
		upyun = $('#typecho-option-item-upyunbucket-17,#typecho-option-item-upyundomain-18,#typecho-option-item-upyunuser-19,#typecho-option-item-upyunpwd-20,#typecho-option-item-upyunkey-21,#typecho-option-item-upyunprefix-22'),
		bcs = $('#typecho-option-item-bcsbucket-23,#typecho-option-item-bcsapikey-24,#typecho-option-item-bcssecretkey-25,#typecho-option-item-bcsprefix-26');
	$("#storage-local").click(function() {
		local.show();
		qiniu.hide();
		upyun.hide();
		bcs.hide();
	});
	$("#storage-qiniu").click(function() {
		local.hide();
		qiniu.show();
		upyun.hide();
		bcs.hide();
	});
	$("#storage-upyun").click(function() {
		local.hide();
		qiniu.hide();
		upyun.show();
		bcs.hide();
	});
	$("#storage-bcs").click(function() {
		local.hide();
		qiniu.hide();
		upyun.hide();
		bcs.show();
	});

	$('#upload').hover(
	function() {
		$(this).css('background-color','#DBDBD6');
	},
	function() {
		$(this).css('background-color','#E9E9E6');
	})

});
</script>

<?php include'footer.php';

} ?>