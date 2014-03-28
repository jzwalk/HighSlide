<?php
include 'header.php';
include 'menu.php';
$phpMaxFilesize = function_exists('ini_get')?trim(ini_get('upload_max_filesize')):0;
if (preg_match("/^([0-9]+)([a-z]{1,2})$/i",$phpMaxFilesize,$matches)) {
    $phpMaxFilesize = strtolower($matches[1].$matches[2].(1 == strlen($matches[2])?'b':''));
}
//导航部数据准备
$data1 = $db->fetchAll($db->select('sort')->from('table.gallery')->order('order', Typecho_Db::SORT_ASC));
for($i=0;$i<count($data1);$i++){
	$sorts[] = $data1[$i]['sort'];
}
if(!empty($sorts)){
	$groups = array_unique($sorts);
	sort($groups);
	$group1 = array_shift($groups);
	$galleries1 = $db->fetchAll($db->select()->from('table.gallery')->where('sort=?',$group1)->order('order',Typecho_Db::SORT_ASC));
}
//列表部数据准备
$data2 = $db->fetchAll($db->select('image','thumb')->from('table.gallery'));
$images = array();
$thumbs = array();
for($i=0;$i<count($data2);$i++){
	$images[] = $data2[$i]['image'];
	$thumbs[] = $data2[$i]['thumb'];
}
//上传图片显示及排序处理
$filedir = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/';
$files = glob($filedir.'[0-9]*.{gif,jpg,jpeg,png,tiff,bmp,GIF,JPG,JPEG,PNG,TIFF,BMP}',GLOB_BRACE|GLOB_NOSORT);
$thumbfiles = glob($filedir.'thumb_*.{gif,jpg,jpeg,png,tiff,bmp,GIF,JPG,JPEG,PNG,TIFF,BMP}',GLOB_BRACE|GLOB_NOSORT);
if (!empty($thumbfiles)) {
	function filekey($value) {
		return basename($value);
	}
	function thumbkey($value) {
		return end(explode('_',basename($value)));
	}
	$filekeys = array_map('filekey',$files);
	$thumbkeys = array_map('thumbkey',$thumbfiles);
	$files = array_combine($filekeys,$files);
	$thumbfiles = array_combine($thumbkeys,$thumbfiles);
	$files = array_merge_recursive($files,$thumbfiles);
	function array_multi2single($array){
    	static $result = array();
    	foreach ($array as $value) {
        if (is_array($value)) {
            array_multi2single($value);
        }
        else $result[]=$value;
    	}
    return $result;
	}
	$files = array_multi2single($files);
}
?>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-galleries">
                <div class="col-mb-12 typecho-list">
                	<div class="clearfix">
                    	<ul class="typecho-option-tabs">
						<?php if(!empty($sorts)): ?>
							<li<?php if(!isset($request->group)||$group1==$request->get('group')): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php'); ?>"><span class="balloon"><?php echo count($galleries1); ?></span> <?php echo _e('相册组'.$group1); ?></a></li>
							<?php foreach ($groups as $group):
							$galleries = $db->fetchAll($db->select('gid')->from('table.gallery')->where('sort=?',$group)); ?>
							<li<?php if($group==$request->get('group')): ?> class="current"<?php endif; ?>><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php&group='.$group); ?>"><span class="balloon"><?php echo count($galleries); ?></span> <?php echo _e('相册组'.$group); ?></a></li>
                        	<?php endforeach; else: ?>
							<li class="current"><a href="<?php $options->adminUrl('extending.php?panel=HighSlide%2Fmanage-gallery.php'); ?>"><?php _e('相册组'); ?></a></li>
                        	<?php endif; ?>
                        	<li><a href="http://www.jzwalk.com/archives/net/highslide-for-typecho/" title="查看页面相册使用帮助" target="_blank"><?php _e('帮助'); ?></a></li>
                    	</ul>
                	</div>
                </div>
                <div class="col-mb-12 col-tb-7" role="main">
                    <form method="post" name="manage_galleries" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                        <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                        <div class="btn-group btn-drop">
                        <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                        <ul class="dropdown-menu">
                        <li><a lang="<?php _e('你确认要删除这些图片吗?'); ?>" href="<?php $security->index('/action/gallery-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
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
                                <tr class="nodrag">
                                    <th> </th>
									<th><?php _e('缩略图'); ?></th>
									<th><?php _e('图片名称'); ?></th>
									<th><?php _e('图片描述'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
								<?php if(!empty($sorts)):
									if(!isset($request->group)||$group1==$request->get('group')){
									foreach ($galleries1 as $gallery1): ?>
									<tr id="gid-<?php echo $gallery1['gid']; ?>">
                    	                <td><input type="checkbox" value="<?php echo $gallery1['gid']; ?>" name="gid[]"/></td>
										<td><?php echo '<a href="'.$gallery1['image'].'" title="点击查看原图" target="_blank"><img style="max-width:100px;" src="'.$gallery1['thumb'].'" alt="'.$gallery1['name'].'"/></a>'; ?></td>
										<td><a class="balloon-button" href="<?php echo $request->makeUriByRequest('gid='.$gallery1['gid']); ?>" title="点击修改图片"><?php echo $gallery1['name']; ?></a>
										<td><?php echo $gallery1['description']; ?></td>
									</tr>
									<?php endforeach;}else{
            	                    foreach ($groups as $group){
            	                    $galleries = $db->fetchAll($db->select()->from('table.gallery')->where('sort=?',$group)->order('order', Typecho_Db::SORT_ASC));
            	                    	if($group==$request->get('group')){
            	                    	foreach ($galleries as $gallery): ?>
        	                        <tr id="gid-<?php echo $gallery['gid']; ?>">
        	                        	<td><input type="checkbox" value="<?php echo $gallery['gid']; ?>" name="gid[]"/></td>
        	                        	<td><?php echo '<a href="'.$gallery['image'].'" title="点击查看原图" target="_blank"><img style="max-width:100px;" src="'.$gallery['thumb'].'" alt="'.$gallery['name'].'"/></a>'; ?></td>
        	                        	<td><a class="balloon-button" href="<?php echo $request->makeUriByRequest('gid='.$gallery['gid']); ?>" title="点击修改图片"><?php echo $gallery['name']; ?></a>
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
                <link rel="stylesheet" type="text/css" media="all" href="<?php $options->pluginUrl('HighSlide/ias/imgareaselect-animated.css'); ?>" />
                	<div id="tab-files">
                		<div id="upload-panel" class="p">
                			<div class="upload-area" draggable="true"><?php _e('拖放文件到这里<br>或者 %s选择文件上传%s','<a href="###" class="upload-file">','</a>'); ?></div>
                			<ul id="file-list">
                			    <?php if(!empty($files)):
                			    $i=0;
                			    foreach ($files as $file):
                			    $filename = basename($file);
                			    $fileurl = $options->pluginUrl.'/HighSlide/gallery/'.$filename;
                			    if (!in_array($fileurl,$images)&&!in_array($fileurl,$thumbs)): ?>
                			        <li id="queue-<?php echo $i++; ?>" data-name="<?php echo $filename; ?>">
                			    		<?php if(preg_match('/thumb\_/',$filename)): ?>
                			            <img class="preview" style="max-width:100px;float:left;overflow:hidden;" src="<?php echo $fileurl; ?>" alt="<?php echo $filename; ?>"/>
                			            <textarea id="thumb-url" style="padding:0;width:332px;float:right;overflow:hidden;" readonly ><?php echo $fileurl; ?></textarea>
                			            <div class="info" style="clear:both;">
                			                <?php echo number_format(ceil(filesize($file)/1024)); ?> Kb
                			                <a class="delete" href="###" title="<?php _e('删除缩略图'); ?>"><i class="i-delete"></i></a>
                			            </div>
                			    		<?php else: ?>
                			            <img id="uploadimg-queue-<?php echo $i-1; ?>" class="preview" style="max-width:442px;" src="<?php echo $fileurl; ?>" alt="<?php echo $filename; ?>" />
                			            <div class="info" style="position:relative;">
                			                <?php echo number_format(ceil(filesize($file)/1024)); ?> Kb
                			                <a class="crop" href="###" title="<?php _e('截取缩略图'); ?>"><i class="mime-application"></i></a>
                			                <a class="delete" href="###" title="<?php _e('删除原图(同时删除其缩略图)'); ?>"><i class="i-delete"></i></a>
                			                <input type="text" id="image-url" style="padding:0;width:347px;float:right;overflow:hidden;position:absolute;" value="<?php echo $fileurl; ?>" readonly /><div style="clear:both;"></div>
                			            </div>
                			            	<input type="hidden" name="x1" value="" id="x1" />
                			            	<input type="hidden" name="y1" value="" id="y1" />
                			            	<input type="hidden" name="x2" value="" id="x2" />
                			            	<input type="hidden" name="y2" value="" id="y2" />
                			            	<input type="hidden" name="w" value="" id="w" />
                			            	<input type="hidden" name="h" value="" id="h" />
                			            <?php endif; ?>
                			        </li>
                			    <?php endif;endforeach;endif; ?>
                			</ul>
                		</div>
                	</div>
                    <div id="tab-infos"><?php HighSlide_Plugin::form()->render(); ?></div>
                </div>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
?>
<script src="<?php $options->adminUrl('js/moxie.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->adminUrl('js/plupload.js?v=' . $suffixVersion); ?>"></script>
<script src="<?php $options->pluginUrl('HighSlide/imgareaselect.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
	var table = $('.typecho-list-table').tableDnD({
		onDrop:function(){
			var ids = [];
			$('input[type=checkbox]',table).each(function(){
				ids.push($(this).val());
			});
			$.post('<?php $security->index('/action/gallery-edit?do=sort'); ?>',
				$.param({gid:ids}));
			$('tr',table).each(function(i){
				if (i%2) {
					$(this).addClass('even');
				}else{
					$(this).removeClass('even');
				}
			});
		}
	});
	table.tableSelectable({
		checkEl:'input[type=checkbox]',
		rowEl:'tr',
		selectAllEl:'.typecho-table-select-all',
		actionEl:'.dropdown-menu a'
	});
	$('.btn-drop').dropdownMenu({
		btnEl:'.dropdown-toggle',
		menuEl:'.dropdown-menu'
	});
	$('.dropdown-menu button.merge').click(function(){
		var btn = $(this);
		btn.parents('form').attr('action', btn.attr('rel')).submit();
	});
	<?php if (isset($request->mid)): ?>
	$('.typecho-mini-panel').effect('highlight', '#AACB36');
	<?php endif; ?>

    $('.upload-area').bind({
        dragenter:function(){
            $(this).parent().addClass('drag');
        },
        dragover:function(e){
            $(this).parent().addClass('drag');
        },
        drop:function(){
            $(this).parent().removeClass('drag');
        },
        dragend:function(){
            $(this).parent().removeClass('drag');
        },
        dragleave:function(){
            $(this).parent().removeClass('drag');
        }
    });

    function fileUploadStart(file){
        $('<li id="'+file.id+'" class="loading">'
            +file.name+'</li>').prependTo('#file-list');
    }
    function fileUploadError(error){
        var file = error.file,code = error.code,word;
        switch (code) {
            case plupload.FILE_SIZE_ERROR:
                word = '<?php _e('文件大小超过限制'); ?>';
                break;
            case plupload.FILE_EXTENSION_ERROR:
                word = '<?php _e('文件扩展名不被支持'); ?>';
                break;
            case plupload.FILE_DUPLICATE_ERROR:
                word = '<?php _e('文件已经上传过'); ?>';
                break;
            case plupload.HTTP_ERROR:
            default:
                word = '<?php _e('上传出现错误'); ?>';
                break;
        }
        var fileError = '<?php _e('%s 上传失败'); ?>'.replace('%s',file.name),
            li,exist = $('#'+file.id);
        if (exist.length>0){
            li = exist.removeClass('loading').html(fileError);
        }else{
            li = $('<li>'+fileError+'<br />'+word+'</li>').prependTo('#file-list');
        }
        li.effect('highlight',{color:'#FBC2C4'},2000,function(){
            $(this).remove();
        });
    }

    var completeFile = null;
    function fileUploadComplete(id,url,data){
        var li = $('#'+id).removeClass('loading').data('name',data.name)
            .html('<input type="hidden" name="imgname" value="'+data.name+'" />'
                +'<img id="uploadimg-'+id+'" class="preview" src="<?php $options->pluginUrl('/HighSlide/gallery/'); ?>'+data.name+'" alt="'+data.title+'" style="max-width:442px;"/><div class="info" style="position:absolute;">'+data.bytes
                +' <a class="crop" href="###" title="<?php _e('截取缩略图'); ?>"><i class="mime-application"></i></a>'
                +' <a class="delete" href="###" title="<?php _e('删除原图(同时删除其缩略图)'); ?>"><i class="i-delete"></i></a></div>'
                +' <input type="text" id="image-url" style="padding:0;width:347px;float:right;overflow:hidden;position:relative;" value="<?php $options->pluginUrl('/HighSlide/gallery/'); ?>'+data.name+'" readonly /><div style="clear:both;"></div>'
                +'<input type="hidden" name="x1" value="" id="x1" />'
                +'<input type="hidden" name="y1" value="" id="y1" />'
                +'<input type="hidden" name="x2" value="" id="x2" />'
                +'<input type="hidden" name="y2" value="" id="y2" />'
                +'<input type="hidden" name="w" value="" id="w" />'
                +'<input type="hidden" name="h" value="" id="h" />')
            .effect('highlight',1000);
        $('#image-0-1').val('<?php $options->pluginUrl('/HighSlide/gallery/'); ?>'+data.name+'');
        iasEffectEvent(li);
        imageDeleteEvent(li);
        thumbCropEvent(li);
        autoSelectEvent(li);
        if (!completeFile) {
            completeFile = data;
        }
    }

        var uploader = new plupload.Uploader({
            browse_button   :   $('.upload-file').get(0),
            url             :   '<?php $security->index('/action/gallery-edit?do=upload'); ?>',
            runtimes        :   'html5,flash,html4',
            flash_swf_url   :   '<?php $options->adminUrl('js/Moxie.swf'); ?>',
            drop_element    :   $('.upload-area').get(0),
            filters         :   {
                max_file_size       :   '<?php echo $phpMaxFilesize ?>',
                mime_types          :   [{'title' : '<?php _e('允许上传的文件'); ?>', 'extensions' : 'gif,jpg,jpeg,png,tiff,bmp'}],
                prevent_duplicates  :   true
            },

            init            :   {
                FilesAdded      :   function (up, files) {
                    plupload.each(files, function(file) {
                        fileUploadStart(file);
                    });

                    completeFile = null;
                    uploader.start();
                },

                UploadComplete  :   function () {
                },

                FileUploaded    :   function (up, file, result) {
                    if (200 == result.status) {
                        var data = $.parseJSON(result.response);

                        if (data) {
                            fileUploadComplete(file.id, data[0], data[1]);
                            return;
                        }
                    }

                    fileUploadError({
                        code : plupload.HTTP_ERROR,
                        file : file
                    });
                },

                Error           :   function (up, error) {
                    fileUploadError(error);
                }
            }
        });

        uploader.init();

    function imageDeleteEvent(el){
        $('.delete',el).click(function () {
            var name = $(this).parents('li').data('name');
        	var id = $(this).parents('li').attr('id');
            if (confirm('<?php _e('确认删除图片 %s 吗?'); ?>'.replace('%s',name))) {
                $.post('<?php $security->index('/action/gallery-edit'); ?>',
                {'do':'remove','imgname':name},
                function(){
                	$('img#uploadimg-'+id+'').imgAreaSelect({remove:true});
                	$(el).fadeOut(function(){
                		$(this).remove();
                	});
                	$('#thumb-'+id+',li[data-name="thumb_'+name+'"]').fadeOut(function(){
                		$(this).remove();
                	});
                });
            }
            return false;
        });
    }

    function thumbCropEvent(el){
        $('.crop',el).click(function(){
        	var name = $(this).parents('li').data('name');
        	var id = $(this).parents('li').attr('id');
        	var x1 = $('#x1',el).val();
        	var y1 = $('#y1',el).val();
        	var x2 = $('#x2',el).val();
        	var y2 = $('#y2',el).val();
        	var w = $('#w',el).val();
        	var h = $('#h',el).val();
			if(x1==""||y1==""||x2==""||y2==""||w==""||h==""){
				alert("请拖选对应的图片区域");
				return false;
			}
        	if ($('#thumb-'+id+'').length==0&&$('li[data-name="thumb_'+name+'"]').length==0){
        		$('<li id="thumb-'+id+'" class="loading"></li>').appendTo($(this).parents('li'));
        	}
            $('img[id^="uploadimg-"]').imgAreaSelect({hide:true});
        	$.post('<?php $security->index('/action/gallery-edit'); ?>',
        		{'do':'crop','imgname':name,'x1':x1,'y1':y1,'w':w,'h':h},
        		function(data){
        		if ($('li[data-name="thumb_'+name+'"]').length==0){
        			var li = $('#thumb-'+id+'').removeClass('loading').data('name','thumb_'+name)
        			.html('<img class="preview" style="max-width:100px;float:left;overflow:hidden;" src="<?php $options->pluginUrl('/HighSlide/gallery/thumb_'); ?>'+name+'?s='+Math.floor(Math.random()*50)+'" alt="thumb_'+name+'" />'
            	    +'<textarea id="thumb-url" style="padding:0;width:332px;float:right;overflow:hidden;" readonly ><?php $options->pluginUrl('/HighSlide/gallery/thumb_'); ?>'+name+'</textarea>'
        			+'<div class="info" style="clear:both;">'+data.bytes+' <a class="delete" href="###" title="<?php _e('删除缩略图'); ?>"><i class="i-delete"></i></a></div>')
            	    .effect('highlight',1000);
            	}else{
            	    var li = $('li[data-name="thumb_'+name+'"]').html('<img class="preview" style="max-width:100px;float:left;overflow:hidden;" src="<?php $options->pluginUrl('/HighSlide/gallery/thumb_'); ?>'+name+'?s='+Math.floor(Math.random()*50)+'" alt="thumb_'+name+'" />'
            	    +'<textarea id="thumb-url" style="padding:0;width:332px;float:right;overflow:hidden;" readonly ><?php $options->pluginUrl('/HighSlide/gallery/thumb_'); ?>'+name+'</textarea>'
            	    +'<div class="info" style="clear:both;">'+data.bytes+' <a class="delete" href="###" title="<?php _e('删除缩略图'); ?>"><i class="i-delete"></i></a>')
            	    .effect('highlight',1000);
            	}
        		$('#image-0-1').val('<?php $options->pluginUrl('/HighSlide/gallery/'); ?>'+name+'');
        		$('#thumb-0-2').val('<?php $options->pluginUrl('/HighSlide/gallery/thumb_'); ?>'+name+'');
        		imageDeleteEvent(li);
        		autoSelectEvent(li);
        		});
            return false;
        });
    }

    function iasEffectEvent(el){
        var id = $(el).attr('id');
    	var ias = $('img#uploadimg-'+id+'',el).imgAreaSelect({
        	handles:true,
        	instance:true,
        	classPrefix:'ias-'+id+' ias',
        	onSelectEnd:function(img,selection){
				$('#x1',el).val(selection.x1);
				$('#y1',el).val(selection.y1);
				$('#x2',el).val(selection.x2);
				$('#y2',el).val(selection.y2);
				$('#w',el).val(selection.width);
				$('#h',el).val(selection.height);
        	}
    	});
    }

    function autoSelectEvent(el){
    	$('#image-url,#thumb-url',el).click(function(){
        	$(this).select();
    	});
    }

    $('#file-list li').each(function(){
        iasEffectEvent(this);
        imageDeleteEvent(this);
        thumbCropEvent(this);
        autoSelectEvent(this);
    });

	$('#file-list').css({'max-height':'5000px'});
});
</script>
<?php include 'footer.php'; ?>