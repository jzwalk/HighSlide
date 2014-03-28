<?php
/**
 * 无缝集成HighSlide双核版实现自动化弹窗与页面相册功能。
 * @package HighSlide
 * @author 羽中
 * @version 1.4.5++
 * for Typecho0.9 in-dev version (14.3.14)
 * @link http://www.jzwalk.com/archives/net/highslide-for-typecho
 */
class HighSlide_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
		$info = HighSlide_Plugin::galleryinstall();
		Helper::addPanel(3, 'HighSlide/manage-gallery.php', _t('页面相册'), _t('配置页面相册 <span style="color:#999;">(HighSlide全功能版核心支持)</span>'), 'administrator');
		Helper::addAction('gallery-edit', 'HighSlide_Action');
        Typecho_Plugin::factory('Widget_Archive')->header = array('HighSlide_Plugin', 'headlink');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('HighSlide_Plugin', 'footlink');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('HighSlide_Plugin', 'autohighslide');
        Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('HighSlide_Plugin', 'autohighslide');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('HighSlide_Plugin', 'jshelper');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('HighSlide_Plugin', 'jshelper');
        Typecho_Plugin::factory('admin/footer.php')->end = array('HighSlide_Plugin', 'optionshift');
		return _t($info);
    }
   
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
		Helper::removeAction('gallery-edit');
		Helper::removePanel(3, 'HighSlide/manage-gallery.php');
	}
   
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
    	$mode = new Typecho_Widget_Helper_Form_Element_Radio('mode',
    		array('highslide.packed.js'=>_t('基础版 <span style="color:#999;font-size:0.92857em;">(25.2K)支持单图弹窗</span>'),'highslide-full.packed.js'=>_t('全功能版 <span style="color:#999;font-size:0.92857em;">(46.8K)支持套图幻灯/html弹窗/页面相册等</span>')),'highslide.packed.js',_t('核心选择'));
    	$form->addInput($mode);

    	$rpmode = new Typecho_Widget_Helper_Form_Element_Radio('rpmode',
    		array('ahref'=>_t('链接图片'),'imgsrc'=>_t('所有图片')),'ahref',_t('应用模式'),NULL);
    	$form->addInput($rpmode);

    	$rplist = new Typecho_Widget_Helper_Form_Element_Checkbox('rplist',
    		array('index'=>_t('首页'),'post'=>_t('文章页'),'page'=>_t('独立页'),'archive'=>_t('索引页')),array('index','post','page','archive'),_t('应用范围'),NULL);
    	$form->addInput($rplist);

    	$lang = new Typecho_Widget_Helper_Form_Element_Radio('lang',
    		array('chs'=>_t('中文'),'eng'=>_t('英文')),'chs',_t('提示语言'));
    	$form->addInput($lang);

    	$outline= new Typecho_Widget_Helper_Form_Element_Radio('outline',
    		array(''=>_t('无边框'),'rounded-white'=>_t('圆角白'),'rounded-black'=>_t('圆角黑'),'glossy-dark'=>_t('亮泽黑'),'outer-glow'=>_t('外发光'),'beveled'=>_t('半透明')),'',_t('边框风格'));
    	$form->addInput($outline);

    	$butn = new Typecho_Widget_Helper_Form_Element_Radio('butn',
    		array('false'=>_t('不显示'),'true'=>_t('显示')),'false',_t('关闭按钮'));
    	$form->addInput($butn);

    	$ltext = new Typecho_Widget_Helper_Form_Element_Text('ltext',
    		NULL,'&copy; '.$_SERVER['HTTP_HOST'].'',_t('角标文字'),_t('弹窗左上角logo标识，留空则不显示'));
    	$ltext->input->setAttribute('class','mini');
    	$form->addInput($ltext);

    	$capt = new Typecho_Widget_Helper_Form_Element_Radio('capt',
    		array(''=>_t('不显示'),'this.a.title'=>_t('显示链接title'),'this.thumb.alt'=>_t('显示图片alt')),'',_t('图片说明'),_t('例：&#60;a href="http://xx.jpg" title="图片说明写这"&#62;&#60;img src="http://xxx.jpg" alt="或者写这显示"/&#62;&#60;/a&#62;<p class="advanced" style="color:#467B96;font-weight:bold;">全功能版设置 ———————————————————————————————————————</p>'));
    	$form->addInput($capt);

    	$trans = new Typecho_Widget_Helper_Form_Element_Radio('trans',
    		array('expand'=>_t('缩放式'),'fade'=>_t('渐变式')),'expand',_t('动画效果'));
    	$form->addInput($trans);

    	$align = new Typecho_Widget_Helper_Form_Element_Radio('align',
    		array('default'=>_t('默认'),'center'=>_t('居中')),'default',_t('弹窗位置'));
    	$form->addInput($align);

    	$opac = new Typecho_Widget_Helper_Form_Element_Text('opac',
    		NULL,'0.65',_t('背景遮罩'),_t('可填入0~1之间小数，代表透明至纯黑'));
    	$opac->input->setAttribute('class','mini');
    	$form->addInput($opac->addRule('isFloat',_t('请填写数字')));

    	$slide = new Typecho_Widget_Helper_Form_Element_Radio('slide',
    		array('false'=>_t('关闭'),'true'=>_t('开启')),'true',_t('幻灯按钮'));
    	$form->addInput($slide);

    	$nextimg = new Typecho_Widget_Helper_Form_Element_Radio('nextimg',
    		array('false'=>_t('否'),'true'=>_t('是')),'false',_t('自动翻页'),_t('开启后点击图片为显示下一张'));
    	$form->addInput($nextimg);

    	$cpos = new Typecho_Widget_Helper_Form_Element_Radio('cpos',
    		array(''=>_t('不显示'),'caption'=>_t('底部显示'),'heading'=>_t('顶部显示')),'',_t('图片序数'));
    	$form->addInput($cpos);

    	$wrap = new Typecho_Widget_Helper_Form_Element_Checkbox('wrap',
    		array('draggable-header'=>_t('标题栏 <span style="color:#999;font-size:0.92857em;">支持&#60;hs title="标题"&#62;显示</span>'),'no-footer'=>_t('无拉伸')),NULL,_t('html弹窗效果'));
    	$form->addInput($wrap);

    	$gallery= new Typecho_Widget_Helper_Form_Element_Select('gallery',
    		array('gallery-horizontal-strip'=>_t('连环画册'),'gallery-thumbstrip-above'=>_t('黑色影夹'),'gallery-vertical-strip'=>_t('时光胶带'),'gallery-in-box'=>_t('纯白记忆'),'gallery-floating-thumbs'=>_t('往事片段'),'gallery-floating-caption'=>_t('沉默注脚'),'gallery-controls-in-heading'=>_t('岁月名片'),'gallery-in-page'=>_t('幻影橱窗(单相册)')),'gallery-horizontal-strip',_t('页面相册效果'),_t('页面相册使用独立的套装效果，不受以上设置影响'));
    	$form->addInput($gallery);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 数据库初始化处理
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
	public static function galleryinstall()
	{
		$installdb = Typecho_Db::get();
		$type = explode('_',$installdb->getAdapterName());
		$type = array_pop($type);
		$prefix = $installdb->getPrefix();
		$scripts = file_get_contents('usr/plugins/HighSlide/'.$type.'.sql');
		$scripts = str_replace('typecho_',$prefix,$scripts);
		$scripts = str_replace('%charset%','utf8',$scripts);
		$scripts = explode(';',$scripts);
		try {
			foreach ($scripts as $script) {
				$script = trim($script);
				if ($script) {
					$installdb->query($script,Typecho_Db::WRITE);
				}
			}
			return _t('建立页面相册数据表，插件启用成功');
		} catch (Typecho_Db_Exception $e) {
			$code = $e->getCode();
			if(('Mysql'==$type&&1050==$code)||
					('SQLite'==$type&&('HY000'==$code||1==$code))) {
				try {
					$script = 'SELECT `gid`, `name`, `thumb`, `sort`, `image`, `description`, `order` from `'.$prefix.'gallery`';
					$installdb->query($script,Typecho_Db::READ);
					return _t('检测到页面相册数据表，插件启用成功');
				} catch (Typecho_Db_Exception $e) {
					$code = $e->getCode();
					throw new Typecho_Plugin_Exception(_t('数据表检测失败，插件启用失败。错误号：'.$code));
				}
			} else {
				throw new Typecho_Plugin_Exception(_t('数据表建立失败，插件启用失败。错误号：'.$code));
			}
		}
	}

    /**
     * 页面相册表单构建
     *
     * @access public
     * @param string $action
     * @return Typecho_Widget_Helper_Form
     */
	public static function form($action = NULL)
	{
		$options = Helper::options();
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/gallery-edit',$options->index),
		Typecho_Widget_Helper_Form::POST_METHOD);

		$image = new Typecho_Widget_Helper_Form_Element_Text('image',
			NULL,"http://", _t('原图地址*'));
		$form->addInput($image);

		$thumb = new Typecho_Widget_Helper_Form_Element_Text('thumb',
			NULL,"http://",_t('缩略图地址*'));
		$form->addInput($thumb);

		$name = new Typecho_Widget_Helper_Form_Element_Text('name',
			NULL,NULL,_t('图片名称'));
		$form->addInput($name);

		$description = new Typecho_Widget_Helper_Form_Element_Textarea('description',
			NULL,NULL,_t('图片描述'),_t('用于前台展示图片说明，推荐填写'));
		$form->addInput($description);

		$sort = new Typecho_Widget_Helper_Form_Element_Text('sort',
			NULL,"1",_t('相册组*'),_t('输入数字，对应[GALLERY-数字]方式调用'));
		$sort->input->setAttribute('style','width:60px');
		$form->addInput($sort);

		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);

		$gid = new Typecho_Widget_Helper_Form_Element_Hidden('gid');
		$form->addInput($gid);

		$submit = new Typecho_Widget_Helper_Form_Element_Submit();
    	$submit->input->setAttribute('class','btn');
		$form->addItem($submit);

        //更新模式
		$request = Typecho_Request::getInstance();
        if (isset($request->gid)&&'insert'!=$action) {
			$db = Typecho_Db::get();
			$prefix = $db->getPrefix();
            $gallery = $db->fetchRow($db->select()->from($prefix.'gallery')->where('gid=?', $request->gid));
            if (!$gallery) {
                throw new Typecho_Widget_Exception(_t('图片不存在'), 404);
            }
            $thumb->value($gallery['thumb']);
            $image->value($gallery['image']);
            $sort->value($gallery['sort']);
            $name->value($gallery['name']);
            $description->value($gallery['description']);
            $do->value('update');
            $gid->value($gallery['gid']);
            $submit->value(_t('修改图片'));
            $_action = 'update';
        }else{
            $do->value('insert');
            $submit->value(_t('添加图片'));
            $_action = 'insert';
        }
        if (empty($action)) {
            $action = $_action;
        }

        //验证规则
        if ('insert'==$action||'update'==$action) {
			$thumb->addRule('required',_t('缩略图地址不能为空'));
			$image->addRule('required',_t('原图地址不能为空'));
			$sort->addRule('required',_t('相册组不能为空'));
			$thumb->addRule('url',_t('请输入合法的图片地址'));
			$image->addRule('url',_t('请输入合法的图片地址'));
			$sort->addRule('isInteger',_t('请输入一个整数数字'));
        }
        if ('update'==$action) {
            $gid->addRule('required',_t('图片主键不存在'));
            $gid->addRule(array(new HighSlide_Plugin,'galleryexists'),_t('图片不存在'));
        }

        return $form;
	}

    /**
     * 图片主键数据判断
     * 
     * @access public
     * @param string $gid
     * @return boolean
     */
	public static function galleryexists($gid)
	{
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$gallery = $db->fetchRow($db->select()->from($prefix.'gallery')->where('gid=?', $gid)->limit(1));
		return $gallery?true:false;
	}

    /**
     * 自动替换标签解析
     * 
     * @access public
     * @param string $content
     * @return string
     */
    public static function autohighslide($content,$widget,$lastResult)
    {
    	$content = empty($lastResult)?$content:$lastResult;
        $settings = Helper::options()->plugin('HighSlide');
        $istype = $settings->rplist;
        if (!empty($istype)) {
        	$index = (in_array('index',$istype))?'index':'';
        	$archive = (in_array('archive',$istype))?'archive':'';
        	$post = (in_array('post',$istype))?'post':'';
        	$page = (in_array('page',$istype))?'page':'';
        	if ($widget->is(''.$index.'')||$widget->is(''.$archive.'')||$widget->is(''.$post.'')||$widget->is(''.$page.'')) {
        		$pattern = '/<a(.*?)href\=\"([^\s]+)\.(jpg|gif|png|bmp)\"(.*?)>(.*?)<\/a>/si';
        		$replacement = '<a$1href="$2.$3" class="highslide" onclick="return hs.expand(this,{slideshowGroup:\'images\'})"$4>$5</a>';
        		$content = preg_replace($pattern,$replacement,$content);
    			if ($settings->rpmode=='imgsrc') {
    				$pattern = '/(<img[^>]+src\s*=\s*"?([^>"\s]+)"?[^>]*>)(?!<\/a>)/si';
    				$replacement = '<a href="$2" class="highslide" onclick="return hs.expand(this,{slideshowGroup:\'images\'})">$1</a>';
    				$content = preg_replace($pattern,$replacement,$content);
    			}
    			$content = preg_replace_callback('/<a(.*?)href\=\"([^\s]+)\/attachment\/(\d*|n)\/\"(.*?)>/i',array('HighSlide_Plugin','linkparse'),$content);
    		}
        }
    	if ($widget->is('page')&&$settings->mode=='highslide-full.packed.js') {
    		$content = preg_replace_callback("/\[GALLERY\-([\d|\,]*?)\]/i",array('HighSlide_Plugin','galleryparse'),$content);
    	}
    	if ($settings->mode=='highslide-full.packed.js') {
    		$content = preg_replace_callback("/\s*<(hs)(\s*[^>]*)>(.*?)<\/\\1>\s*/si", array('HighSlide_Plugin','htmlparse'),$content);
    	}
        return $content;
    }

    /**
     * 页面相册回调解析
     * 
     * @access public
     * @param array $matches
     * @return string
     */
    public static function galleryparse($matches)
    {
        $settings = Helper::options()->plugin('HighSlide');
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$tmp = '';
		$cover = '';
		$sorts = explode(',',trim($matches[1]));
		foreach ($sorts as $sort) {
			$gallerys = $db->fetchAll($db->select()->from($prefix.'gallery')->where('sort=?',''.$sort.'')->order($prefix.'gallery.order',Typecho_Db::SORT_ASC));
			if (!empty($gallerys)) {
				$coversets = array(array_shift($gallerys));
				foreach ($coversets as $coverset) {
					if ($settings->gallery=='gallery-in-page') {
						$cover.='<a id="thumb'.$sort.'" class="highslide" href="'.$coverset['image'].'" title="'.$coverset['description'].'" onclick="return hs.expand(this,inPageOptions)"><img src="'.$coverset['thumb'].'" alt="'.$coverset['description'].'"/></a> ';
					}
					elseif ($settings->gallery=='gallery-controls-in-heading') {
						$cover.='<a id="thumb'.$sort.'" class="highslide" href="'.$coverset['image'].'" title="'.$coverset['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$coverset['thumb'].'" alt="'.$coverset['description'].'"/></a><div class="highslide-heading">'.$coverset['description'].'</div> ';
					}
					elseif ($settings->gallery=='gallery-in-box') {
						$cover.='<a id="thumb'.$sort.'" class="highslide" href="'.$coverset['image'].'" title="'.$coverset['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$coverset['thumb'].'" alt="'.$coverset['description'].'"/></a><div class="highslide-caption">'.$coverset['description'].'</div> ';
					}else{
						$cover.='<a id="thumb'.$sort.'" class="highslide" href="'.$coverset['image'].'" title="'.$coverset['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$coverset['thumb'].'" alt="'.$coverset['description'].'"/></a> ';
					}
				}
				foreach ($gallerys as $gallery) {
					if ($settings->gallery=='gallery-in-page') {
						$tmp.='<a class="highslide" href="'.$gallery['image'].'" title="'.$gallery['description'].'" onclick="return hs.expand(this,inPageOptions)"><img src="'.$gallery['thumb'].'" alt="'.$gallery['description'].'"/></a>';
					}
					elseif ($settings->gallery=='gallery-controls-in-heading') {
						$tmp.='<a class="highslide" href="'.$gallery['image'].'" title="'.$gallery['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$gallery['thumb'].'" alt="'.$gallery['description'].'"/></a><div class="highslide-heading">'.$gallery['description'].'</div>';
					}
					elseif ($settings->gallery=='gallery-in-box') {
						$tmp.='<a class="highslide" href="'.$gallery['image'].'" title="'.$gallery['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$gallery['thumb'].'" alt="'.$gallery['description'].'"/></a><div class="highslide-caption">'.$gallery['description'].'</div>';
					} else {
						$tmp.='<a class="highslide" href="'.$gallery['image'].'" title="'.$gallery['description'].'" onclick="return hs.expand(this,{slideshowGroup:\'group'.$sort.'\'})"><img src="'.$gallery['thumb'].'" alt="'.$gallery['description'].'"/></a>';
					}
				}
			}
		}
		$container = '<div class="hidden-container">'.$tmp.'</div>';
		if ($settings->gallery=='gallery-in-page') {
			$output = '<div id="gallery-area" style="width: 620px; height: 520px; margin: 0 auto; border: 1px solid silver"><div class="hidden-container">'.$cover.$tmp.'</div></div>';
		}else{
			$output = '<div class="highslide-gallery">'.$cover.$container.'</div>';
		}
		return $output;
 	}

    /**
     * html弹框回调解析
     * 
     * @access public
     * @param array $matches
     * @return string
     */
    public static function htmlparse($matches)
    {
        $settings = Helper::options()->plugin('HighSlide');
		$param = trim($matches[2]);
		$id = 'highslide-html';
		$text = 'text';
		$title = '';
		$ajax = '';
		$addt = '';
		$width = '';
		$height = '';
        $Movetext = 'Move';
        $Movetitle = 'Move';
        $Closetext = 'Close';
        $Closetitle = 'Close (esc)';
        $Resizetitle = 'Resize';
        if ($settings->lang == 'chs') {
        	$Movetext = '移动';
        	$Movetitle = '移动';
        	$Closetext = '关闭';
        	$Closetitle = '关闭 (esc)';
        	$Resizetitle = '拉伸';
        }
		if (!empty($param)) {
			if (preg_match("/id=[\"']([\w-]*)[\"']/i", $param, $out)) {
				$id = trim($out[1])==''?$id:trim($out[1]);
			}
			if (preg_match("/text=[\"'](.*?)[\"']/si", $param, $out)) {
				$text = trim($out[1])==''?$text:trim($out[1]);
			}
			if (preg_match("/title=[\"'](.*?)[\"']/si", $param, $out)) {
				$title = trim($out[1])==''?$title:trim($out[1]);
			}
			if (preg_match("/ajax=[\"'](.*?)[\"']/si", $param, $out)) {
				$ajax = trim($out[1])==''?$title:trim($out[1]);
			}
			if (preg_match("/width=[\"']([\w-]*)[\"']/i", $param, $out)) {
				$width = trim($out[1])==''?$width:trim($out[1]);
			}
			if (preg_match("/height=[\"']([\w-]*)[\"']/i", $param, $out)) {
				$height = trim($out[1])==''?$height:trim($out[1]);
			}
		}
		if ($settings->wrap) {
			$addt = (in_array('draggable-header',$settings->wrap))?',headingText:\''.$title.'\'':'';
		}
		$href = ($ajax=='')?'#':$ajax;
		$shift = ($ajax=='')?'contentId:\''.$id.'\''.$addt.'':'objectType:\'ajax\'';
		$output = "\n";
		$output .= '<a href="'.$href.'" onclick="return hs.htmlExpand(this,{'.$shift.'})" class="highslide">'.$text.'</a>';
		$output .= '<div class="highslide-html-content" id="'.$id.'" style="width:'.$width.';height:'.$height.';">';
		$output .= '<div class="highslide-header"><ul><li class="highslide-move"><a href="#" onclick="return false" title="'.$Movetitle.'"><span>'.$Movetext.'</span></a></li>';
		$output .= '<li class="highslide-close"><a href="#" onclick="return hs.close(this)" title="'.$Closetitle.'"><span>'.$Closetext.'</span></a></li></ul></div>';
		$output .= '<div class="highslide-body">'.trim($matches[3]).'</div>';
		$output .= '<div class="highslide-footer"><div><span class="highslide-resize" title="'.$Resizetitle.'"><span></span></span></div></div>';
		$output .= '</div>';
		return $output;
	}

    /**
     * 附件链接回调解析
     * 
     * @access public
     * @param array $matches
     * @return string
     */
    public static function linkparse($matches)
    {
		$db = Typecho_Db::get();
		$cid = $matches[3];
		$attach = $db->fetchRow($db->select()->from('table.contents')->where('type=\'attachment\' AND cid=?',$cid));
		$attach_data = unserialize($attach['text']);
		$output = '<a'.$matches[1].'href="'.Typecho_Common::url($attach_data['path'], Helper::options()->siteUrl).'" class="highslide" onclick="return hs.expand(this,{slideshowGroup:\'images\'})"'.$matches[4].'>';
        return $output;
    }

    /**
     * 图片链接帮助脚本
     *
     * @access public
     * @param string $insert
     * @return void
     */
    public static function jshelper($insert)
    {
    	$settings = Helper::options()->plugin('HighSlide');
    	$insert = '';
		if ($settings->rpmode=='ahref') {
    		if (Helper::options()->markdown=='1') {
    			$insert = "
	<script type=\"text/javascript\">
	$(function() {
	Typecho.insertFileToEditor = function (file, url, isImage) {
        	var textarea = $('#text'), sel = textarea.getSelection(),
		html = isImage ? '[![' + file + '](' + url + ')](' + url + ' \"' + file + '\")': '[' + file + '](' + url + ')',
		offset = (sel ? sel.start : 0) + html.length;
		textarea.replaceSelection(html);
		textarea.setSelection(offset, offset);
	};
	});
	</script>";
    		}else{
				$insert = "
	<script type=\"text/javascript\">
	$(function() {
	Typecho.insertFileToEditor = function (file, url, isImage) {
        	var textarea = $('#text'), sel = textarea.getSelection(),
		html = isImage ? '<a href=\"' + url + '\" title=\"' + file + '\" target=\"_blank\"><img src=\"' + url + '\" alt=\"' + file + '\" /></a>':'<a href=\"' + url + '\">' + file + '</a>',
		offset = (sel ? sel.start : 0) + html.length;
		textarea.replaceSelection(html);
		textarea.setSelection(offset, offset);
	};
	});
	</script>";
			}
		}
		echo $insert;
    }

    /**
     * 插件设置效果脚本
     *
     * @access public
     * @param string $jquery
     * @return void
     */
    public static function optionshift($jquery)
    {
    	$jquery = "
	<script type=\"text/javascript\">
	$(function(){
		if($('input#mode-highslide-full-packed-js').is(':checked')){
			return false;
		}
		else{
			$('.advanced').attr('style','color:#999;font-weight:bold');
			$('#typecho-option-item-trans-8,#typecho-option-item-align-9,#typecho-option-item-opac-10,#typecho-option-item-slide-11,#typecho-option-item-nextimg-12,#typecho-option-item-cpos-13,#typecho-option-item-wrap-14,#typecho-option-item-gallery-15')
				.attr('style','color:#999')
				.find('input,select').attr('disabled','disabled');
			$('input#mode-highslide-full-packed-js').click(function(){
				$('.advanced').attr('style','color:#467B96;font-weight:bold').fadeOut(100).fadeIn(100);
 				$('#typecho-option-item-trans-8,#typecho-option-item-align-9,#typecho-option-item-opac-10,#typecho-option-item-slide-11,#typecho-option-item-nextimg-12,#typecho-option-item-cpos-13,#typecho-option-item-wrap-14,#typecho-option-item-gallery-15')
 					.removeAttr('style').fadeOut(100).fadeIn(100)
 					.find('input,select').removeAttr('disabled');
			});
			$('input#mode-highslide-packed-js').click(function(){
				$('.advanced').attr('style','color:#999;font-weight:bold').fadeOut(100).fadeIn(100);
 				$('#typecho-option-item-trans-8,#typecho-option-item-align-9,#typecho-option-item-opac-10,#typecho-option-item-slide-11,#typecho-option-item-nextimg-12,#typecho-option-item-cpos-13,#typecho-option-item-wrap-14,#typecho-option-item-gallery-15')
 					.attr('style','color:#999').fadeOut(100).fadeIn(100)
 					.find('input,select').attr('disabled','disabled');
			});
		}
	});
	</script>";
		echo $jquery;
    }

    /**
     * 主头部输出HS样式
     *
     * @access public
     * @param string $cssurl
     * @return void
     */
    public static function headlink($cssurl)
    {
        $settings = Helper::options()->plugin('HighSlide');
        $archive = Typecho_Widget::widget('Widget_Archive');
        $hsurl = Helper::options()->pluginUrl .'/HighSlide/';
        $cssurl = '<link rel="stylesheet" type="text/css" href="'.$hsurl.'highslide.css" />
		<!--[if lt IE 7]>
		<link rel="stylesheet" type="text/css" href="'.$hsurl.'highslide-ie6.css" />
		<![endif]-->
		';
        if ($settings->mode=='highslide-full.packed.js'&&$archive->is('page')) {
        	if ($settings->gallery == 'gallery-in-page') {
        		$cssurl.= '<style type="text/css">
		.highslide-image {
		border: 1px solid black;
		}
		.highslide-controls {
		width: 90px !important;
		}
		.highslide-controls .highslide-close {
		display: none;
		}
		.highslide-caption {
		padding: .5em 0;
		}
		</style>
		';
			}
        	if ($settings->gallery == 'gallery-vertical-strip') {
        		$cssurl.= '<style type="text/css">
		.highslide-caption {
		width: 100%;
		text-align: center;
		}
		.highslide-close {
		display: none !important;
		}
		.highslide-number {
		display: inline;
		padding-right: 1em;
		color: white;
		}
		</style>
		';
			}
		}
        echo $cssurl;
    }

    /**
     * 主底部输出HS脚本
     *
     * @access public
     * @param string $links
     * @return void
     */
    public static function footlink($links)
    {
        $settings = Helper::options()->plugin('HighSlide');
        $archive = Typecho_Widget::widget('Widget_Archive');
        $hsurl = Helper::options()->pluginUrl .'/HighSlide/';
        $closetitle = ($settings->lang=='chs')?'关闭':'Close';
        $links = '
		<script type="text/javascript" src="'.$hsurl.$settings->mode.'"></script>';
        $links.='
        <script type="text/javascript">
		//<![CDATA[
        hs.graphicsDir = "'.$hsurl.'graphics/";
        hs.fadeInOut = true;';
        if ($settings->ltext=='') {
        	$links.='
        hs.showCredits = false;';
        }
        if ($settings->ltext!=='') {
        	$links.='
        hs.lang.creditsText = "'.$settings->ltext.'";
        hs.lang.creditsTitle = "'.$settings->ltext.'";
        hs.creditsHref = "'.Helper::options()->index.'";';
        }
        if ($settings->lang=='chs') {
        	$links.='
        hs.lang={
        loadingText : "载入中...",
        loadingTitle : "取消",
        closeText : "关闭",
        closeTitle : "关闭 (Esc)",
        previousText : "上一张",
        previousTitle : "上一张 (←键)",
        nextText : "下一张",
        nextTitle : "下一张 (→键)",
        moveTitle : "移动",
        moveText : "移动",
        playText : "播放",
        playTitle : "幻灯播放 (空格键)",
        pauseText : "暂停",
        pauseTitle : "幻灯暂停 (空格键)",
        number : "第%1张 共%2张",
        restoreTitle :    "点击关闭或拖动。左右方向键切换图片。",
        fullExpandTitle : "原始尺寸",
        fullExpandText :  "原图"
        };';
        }

        //非页面弹窗定制
        $type = ($settings->mode=='highslide-full.packed.js')?'post':'single';
        if ($archive->is('index')||$archive->is(''.$type.'')||$archive->is('archive')) {
        	$links.='
        hs.transitions = ["'.$settings->trans.'","crossfade"];';
        	if ($settings->outline!=='') {
        		$links.='
        hs.outlineType = "'.$settings->outline.'";';
        	}
        	if ($settings->outline=='glossy-dark'&&$settings->wrap!==NULL||
        		$settings->outline=='rounded-black'&&$settings->wrap!==NULL) {
        		$links.='
        hs.wrapperClassName = "dark '.implode(" ",$settings->wrap).'";';
        	}
        	if ($settings->outline=='outer-glow'&&$settings->wrap!== NULL) {
        		$links.='
        hs.wrapperClassName = "outer-glow '.implode(" ", $settings->wrap).'";';
        	}
        	if ($settings->outline=='beveled'&&$settings->wrap!==NULL) {
        		$links.='
        hs.wrapperClassName = "borderless '.implode(" ",$settings->wrap).'";';
        	}
        	if ($settings->wrap!==NULL&&
        		$settings->outline!=='glossy-dark'&&
        		$settings->outline!=='rounded-black'&&
        		$settings->outline!=='outer-glow'&&
        		$settings->outline!=='beveled') {
        		$links.='
        hs.wrapperClassName = "'.implode(" ",$settings->wrap).'";';
        	}
        	if ($settings->outline=='glossy-dark'&&$settings->wrap==NULL||
        		$settings->outline=='rounded-black'&&$settings->wrap==NULL) {
        		$links.='
        hs.wrapperClassName = "dark";';
        	}
        	if ($settings->outline=='outer-glow'&&$settings->wrap==NULL) {
        		$links.='
        hs.wrapperClassName = "outer-glow";';
        	}
        	if ($settings->outline=='beveled'&&$settings->wrap==NULL) {
        		$links.='
        hs.wrapperClassName = "borderless";';
        	}
        	if ($settings->butn=='true') {
        		$links.='
        hs.registerOverlay({
		html: \'<div class="closebutton" onclick="return hs.close(this)" title="'.$closetitle.'"></div>\',
		position: "top right",
		fade: 2
		});';
        	}
        	if ($settings->capt!=='') {
        		$links.='
        hs.captionEval = "'.$settings->capt.'";';
        	}
        	if ($settings->mode=='highslide-full.packed.js') {
        		if ($settings->cpos!=='') {
        			$links.='
        hs.numberPosition = "'.$settings->cpos.'";';
        		}
        		if ($settings->opac!=='') {
        			$links.='
        hs.dimmingOpacity = '.$settings->opac.';';
        		}
        		if ($settings->align=='center') {
        			$links.='
        hs.align = "center";';
        		}
        		if ($settings->slide=='true') {
        			$links.='
        if (hs.addSlideshow) hs.addSlideshow({
        slideshowGroup: "images",
        interval: 5000,
        repeat: true,
        useControls: true,
        fixedControls: "fit",
        overlayOptions: {
		opacity: .65,
		position: "bottom center",
		hideOnMouseOut: true
        }
        });';
        		}
        		if ($settings->nextimg=='true') {
        			$links.='
        hs.Expander.prototype.onImageClick = function() {
        return hs.next();
        }';
        		}
        	}
        }

        //页面相册弹窗定制
        elseif ($settings->mode=='highslide-full.packed.js'&&$archive->is('page')) {
        	preg_match_all("/\[GALLERY\-([\d|\,]*?)\]/i",$archive->text,$matches);
    		$groups = explode(',',implode(',',$matches[1]));
        	function groupsalter(&$item, $key, $prefix) {
    			$item = '"'.$prefix.''.$item.'"';
    		}
        	//相册组参数处理
        	array_walk($groups,'groupsalter','group');
        	$group = implode(',',array_unique($groups));
        	$links.='
        hs.transitions = ["expand", "crossfade"];
        if (hs.addSlideshow) hs.addSlideshow({
        interval: 5000,
        repeat: true,
        useControls: true,';
        	if ($settings->gallery=='gallery-horizontal-strip') {
        		$links.='
        slideshowGroup: ['.$group.'],
        overlayOptions: {
		className: "text-controls",
		position: "bottom center",
		relativeTo: "viewport",
		offsetY: -60
        },
        thumbstrip: {
		position: "bottom center",
		mode: "horizontal",
		relativeTo: "viewport"
        }
        });
        hs.align = "center";
        hs.dimmingOpacity = 0.8;
        hs.outlineType = "rounded-white";
        hs.captionEval = "this.thumb.alt";
        hs.marginBottom = 105;
        hs.numberPosition = "caption";';
        	}
        	if ($settings->gallery=='gallery-thumbstrip-above') {
        		$links.='
        slideshowGroup: ['.$group.'],
        fixedControls: "fit",
        overlayOptions: {
		position: "bottom center",
		opacity: .75,
		hideOnMouseOut: true
	    },
        thumbstrip: {
		position: "above",
		mode: "horizontal",
		relativeTo: "expander"
        }
        });
        hs.align = "center";
        hs.outlineType = "glossy-dark";
        hs.wrapperClassName = "dark";
        hs.captionEval = "this.a.title";
        hs.numberPosition = "caption";
        hs.useBox = true;
        hs.width = 600;
        hs.height = 400;';
        	}
        	if ($settings->gallery=='gallery-in-page') {
        		$restoreTitle = ($settings->lang == 'chs')?'点击查看下一张':'Click for next image';
        		$links.='
        overlayOptions: {
		position: "bottom right",
		offsetY: 50
        },
        thumbstrip: {
		position: "above",
		mode: "horizontal",
		relativeTo: "expander"
        }
        });
        hs.restoreCursor = null;
        hs.lang.restoreTitle = "'.$restoreTitle.'";
        var inPageOptions = {
        outlineType: null,
        wrapperClassName: "in-page controls-in-heading",
        thumbnailId: "gallery-area",
        useBox: true,
        width: 600,
        height: 400,
        targetX: "gallery-area 10px",
        targetY: "gallery-area 10px",
        captionEval: "this.a.title",
        numberPosition: "caption"
        }
        hs.addEventListener(window, "load", function() {
        document.getElementById("thumb'.current(explode(',',$matches[1][0])).'").onclick();
        });
        hs.Expander.prototype.onImageClick = function() {
        if (/in-page/.test(this.wrapper.className))	return hs.next();
        }
        hs.Expander.prototype.onBeforeClose = function() {
        if (/in-page/.test(this.wrapper.className))	return false;
        }
        hs.Expander.prototype.onDrag = function() {
        if (/in-page/.test(this.wrapper.className))	return false;
        }
        hs.addEventListener(window, "resize", function() {
        var i, exp;
        hs.getPageSize();
        for (i = 0; i < hs.expanders.length; i++) {
		exp = hs.expanders[i];
		if (exp) {
			var x = exp.x,
				y = exp.y;
			exp.tpos = hs.getPosition(exp.el);
			x.calcThumb();
			y.calcThumb();
		 	x.pos = x.tpos - x.cb + x.tb;
			x.scroll = hs.page.scrollLeft;
			x.clientSize = hs.page.width;
			y.pos = y.tpos - y.cb + y.tb;
			y.scroll = hs.page.scrollTop;
			y.clientSize = hs.page.height;
			exp.justify(x, true);
			exp.justify(y, true);
			exp.moveTo(x.pos, y.pos);
		}
		}
		});';
        	}
        	if ($settings->gallery=='gallery-vertical-strip') {
        		$links.='
        slideshowGroup: ['.$group.'],
		overlayOptions: {
			className: "text-controls",
			position: "bottom center",
			relativeTo: "viewport",
			offsetX: 50,
			offsetY: -5
		},
		thumbstrip: {
			position: "middle left",
			mode: "vertical",
			relativeTo: "viewport"
		}
		});
		hs.registerOverlay({
		html: \'<div class="closebutton" onclick="return hs.close(this)" title="Close"></div>\',
		position: "top right",
		fade: 2
		});
		hs.align = "center";
		hs.dimmingOpacity = 0.8;
		hs.wrapperClassName = "borderless floating-caption";
		hs.captionEval = "this.thumb.alt";
		hs.marginLeft = 100;
		hs.marginBottom = 80;
		hs.numberPosition = "caption";
		hs.lang.number = "%1/%2";';
        	}
        	if ($settings->gallery=='gallery-in-box') {
        		$links.='
        slideshowGroup: ['.$group.'],
		fixedControls: "fit",
		overlayOptions: {
			opacity: 1,
			position: "bottom center",
			hideOnMouseOut: true
		}
		});
		hs.align = "center";
		hs.outlineType = "rounded-white";
		hs.dimmingOpacity = 0.75;
		hs.useBox = true;
		hs.width = 640;
		hs.height = 480;';
        	}
        	if ($settings->gallery=='gallery-floating-thumbs') {
        		$links.='
        slideshowGroup: ['.$group.'],
		fixedControls: "fit",
		overlayOptions: {
			position: "top right",
			offsetX: 200,
			offsetY: -65
		},
		thumbstrip: {
			position: "rightpanel",
			mode: "float",
			relativeTo: "expander",
			width: "210px"
		}
        });
        hs.align = "center";
        hs.outlineType = "rounded-white";
        hs.headingEval = "this.a.title";
        hs.numberPosition = "heading";
        hs.useBox = true;
        hs.width = 600;
        hs.height = 400;';
        	}
        	if ($settings->gallery=='gallery-floating-caption') {
        		$links.='
        slideshowGroup: ['.$group.'],
		fixedControls: "fit",
		overlayOptions: {
			opacity: .6,
			position: "bottom center",
			hideOnMouseOut: true
		}
		});
		hs.align = "center";
		hs.wrapperClassName = "dark borderless floating-caption";
		hs.dimmingOpacity = .75;
		hs.captionEval = "this.a.title";';
			}
			if ($settings->gallery=='gallery-controls-in-heading') {
        		$links.='
        slideshowGroup: ['.$group.'],
		fixedControls: false,
		overlayOptions: {
			opacity: 1,
			position: "top right",
			hideOnMouseOut: false
		}
		});
		hs.align = "center";
		hs.outlineType = "rounded-white";
		hs.wrapperClassName = "controls-in-heading";';
			}
        }
        $links.='
        //]]>
        </script>
        ';
        echo $links;
    }

    /**
     * 创建图片上传路径
     *
     * @access private
     * @param string $path 路径
     * @return boolean
     */
    private static function makehsdir($path)
    {
        if (!@mkdir($path,0777,true)){
            return false;
        }
        $stat = @stat($path);
        $perms = $stat['mode']&0007777;
        @chmod($path,$perms);
        return true;
    }

    /**
     * 图片上传处理函数
     *
     * @access public
     * @param array $file 上传的文件
     * @return mixed
     */
    public static function uploadhandle($file)
    {
        if (empty($file['name'])) {
            return false;
        }
        $imgname = preg_split("(\/|\\|:)",$file['name']);
        $file['name'] = array_pop($imgname);
        $ext = self::getSafeName($file['name']);
        if (!self::checkimgtype($ext)||Typecho_Common::isAppEngine()) {
            return false;
        }
        //创建上传目录
    	$imgdir = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/';
        if (!is_dir($imgdir)) {
            if (!self::makehsdir($imgdir)) {
                return false;
            }
        }
        //获取文件名
    	$imgname = sprintf('%u',crc32(uniqid())).'.'.$ext;
		$imgroot = $imgdir.$imgname;
		$thumbroot = $imgdir.'thumb_'.$imgname;
        if (isset($file['tmp_name'])) {
            //移动上传文件
        	if (!@move_uploaded_file($file['tmp_name'],$imgroot)) {
                return false;
            }
        } elseif (isset($file['bytes'])) {
            //直接写入文件
        	if (!file_put_contents($imgroot,$file['bytes'])) {
                return false;
            }
        }else{
            return false;
        }
        if (!isset($file['size'])) {
            $file['size'] = filesize($imgroot);
        }
		if (file_exists($thumbroot)) {
			@unlink($thumbroot);
		}
        //返回数据信息
		return array(
            'name' => $imgname,
            'title' => $file['name'],
            'size' => $file['size']
        );
	}

    /**
     * 图片删除处理函数
     *
     * @access public
     * @param string $imgname 图片名称
     * @return string
     */
    public static function removehandle($imgname)
    {
		$imgdir = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/';
		$imgroot = $imgdir.$imgname;
		$thumbroot = $imgdir.'thumb_'.$imgname;
        if (file_exists($imgroot)) {
        	if (file_exists($thumbroot)) {
				unlink($thumbroot);
        	}
			return !Typecho_Common::isAppEngine()
			&& @unlink($imgroot);
        }
    }

    /**
     * 图片裁切处理函数
     *
     * @access public
     * @param string $thumb,$image,$width,$height,$xset,$yset,$scale
     * @return string
     */
    public static function crophandle($thumb,$image,$width,$height,$xset,$yset,$scale)
    {
		list($imgwidth,$imgheight,$imgtype) = getimagesize($image);
		$imgtype = image_type_to_mime_type($imgtype);
		$newwidth = ceil($width*$scale);
		$newheight = ceil($height*$scale);
		$newimg = imagecreatetruecolor($newwidth,$newheight);
		switch($imgtype) {
			case "image/gif":
				$source = imagecreatefromgif($image);
				break;
	    	case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$source = imagecreatefromjpeg($image);
				break;
	    	case "image/png":
			case "image/x-png":
				$source = imagecreatefrompng($image);
				break;
  		}
		imagecopyresampled($newimg,$source,0,0,$xset,$yset,$newwidth,$newheight,$width,$height);
		switch($imgtype) {
			case "image/gif":
	  			imagegif($newimg,$thumb);
				break;
      		case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
	  			imagejpeg($newimg,$thumb,100);
				break;
			case "image/png":
			case "image/x-png":
				imagepng($newimg,$thumb);
				break;
    	}
		return $thumb;
	}

    /**
     * 获取安全的文件名
     * 
     * @param string $name
     * @static
     * @access private
     * @return string
     */
    private static function getSafeName(&$name)
    {
        $name = str_replace(array('"','<','>'),'',$name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name,'/')?('a'.$name):str_replace('/','/a',$name);
        $info = pathinfo($name);
        $name = substr($info['basename'],1);
        return isset($info['extension'])?$info['extension']:'';
    }

    /**
     * 图片扩展名检查
     *
     * @access private
     * @param string $ext 扩展名
     * @return boolean
     */
    private static function checkimgtype($ext)
    {
        return in_array($ext,array('gif','jpg','jpeg','png','tiff','bmp'));
    }

}