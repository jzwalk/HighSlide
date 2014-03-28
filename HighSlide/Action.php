<?php
class HighSlide_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;

    /**
     * 添加相册图片
     *
     * @access public
     * @return void
     */
	public function insertgallery()
	{
		if (HighSlide_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		$gallery = $this->request->from('thumb','image','description','sort','name');
		$gallery['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)'=>'maxOrder'))->from($this->prefix.'gallery'))->maxOrder+1;
		$gallery['gid'] = $this->db->query($this->db->insert($this->prefix.'gallery')->rows($gallery));
		$this->widget('Widget_Notice')->highlight('gallery-'.$gallery['gid']);
		$this->widget('Widget_Notice')->set(_t('相册组%s 图片%s 添加成功',
			$gallery['sort'],$gallery['name']),NULL,'success');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=HighSlide%2Fmanage-gallery.php&group='.$gallery['sort'],$this->options->adminUrl));
	}

    /**
     * 更新相册图片
     *
     * @access public
     * @return void
     */
	public function updategallery()
	{
		if (HighSlide_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}
		$gallery = $this->request->from('gid','thumb','image','description','sort','name');
		$this->db->query($this->db->update($this->prefix.'gallery')->rows($gallery)->where('gid = ?',$gallery['gid']));
		$this->widget('Widget_Notice')->highlight('gallery-'.$gallery['gid']);
		$this->widget('Widget_Notice')->set(_t('相册组%s 图片%s 更新成功',
			$gallery['sort'],$gallery['name']),NULL,'success');
		$this->response->redirect(Typecho_Common::url('extending.php?panel=HighSlide%2Fmanage-gallery.php&group='.$gallery['sort'],$this->options->adminUrl));
	}

    /**
     * 删除相册图片
     *
     * @access public
     * @return void
     */
    public function deletegallery()
    {
        $gids = $this->request->filter('int')->getArray('gid');
        $galleries = array();
        $imgdir = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/';
        $files = glob($imgdir.'*.{gif,jpg,jpeg,png,tiff,bmp,GIF,JPG,JPEG,PNG,TIFF,BMP}',GLOB_BRACE);
        $deletecount = 0;
        if ($gids) {
        	$galleries = $this->db->fetchAll($this->db->select('image')->from('table.gallery')->where('gid in ('.implode(',',$gids).')'));
            foreach ($galleries as $gallery) {
            	$imgname = basename($gallery['image']);
            	if (in_array($imgdir.$imgname,$files)) {
            		HighSlide_Plugin::removehandle($imgname);
            	}
            }
            foreach ($gids as $gid) {
                if ($this->db->query($this->db->delete($this->prefix.'gallery')->where('gid=?',$gid))) {
                    $deletecount ++;
                }
            }
        }
        $this->widget('Widget_Notice')->set($deletecount>0?_t('图片已经删除'):_t('没有图片被删除'),NULL,
        	$deletecount>0?'success':'notice');
        $this->response->goBack();
    }

    /**
     * 排序相册图片
     *
     * @access public
     * @return void
     */
    public function sortgallery()
    {
        $galleries = $this->request->filter('int')->getArray('gid');
        if ($galleries) {
			foreach ($galleries as $sort=>$gid) {
				$this->db->query($this->db->update($this->prefix.'gallery')->rows(array('order'=>$sort+1))->where('gid=?',$gid));
			}
        }
        if (!$this->request->isAjax()) {
            $this->response->goBack();
        } else {
            $this->response->throwJson(array('success'=>1,'message'=>_t('图片排序完成')));
        }
    }

    /**
     * 执行上传图片
     *
     * @access public
     * @return void
     */
    public function uploadimage()
    {
        if (!empty($_FILES)) {
            $file = array_pop($_FILES);
            if (0==$file['error']&&is_uploaded_file($file['tmp_name'])) {
                // xhr的send无法支持utf8
                if ($this->request->isAjax()) {
                    $file['name'] = urldecode($file['name']);
                }
            	$result = HighSlide_Plugin::uploadhandle($file);
                if (false !== $result) {
            	$imgurl = $this->options->pluginUrl.'/HighSlide/gallery/'.$result['name'];
				$this->response->throwJson(array($imgurl,array(
					'name'=>$result['name'],
					'title'=>$result['title'],
					'bytes'=>number_format(ceil($result['size']/1024)).' Kb'
					)));
				}
			}
		}
		$this->response->throwJson(false);
	}

    /**
     * 执行删除图片
     *
     * @access public
     * @return void
     */
    public function removeimage()
    {
		$imgnames = $this->request->getArray('imgname');
		if ($imgnames) {
        	foreach ($imgnames as $imgname) {
			HighSlide_Plugin::removehandle($imgname);
			}
        }
        $this->response->throwJson(false);
    }

    /**
     * 执行裁切图片
     *
     * @access public
     * @return void
     */
    public function cropthumbnail()
    {
		$imgnames = $this->request->getArray('imgname');
		$x1s = $this->request->getArray('x1');
		$y1s = $this->request->getArray('y1');
		$ws = $this->request->getArray('w');
		$hs = $this->request->getArray('h');
		if ($imgnames) {
			foreach ($imgnames as $imgname) {
				$imgroot = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/'.$imgname;
				$thumbroot = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HighSlide/gallery/'.'thumb_'.$imgname;
				$imgsize = getimagesize($imgroot);
				$adjust = ($imgsize[0]>442)?$imgsize[0]/442:1;
				foreach ($x1s as $x1) {
					$x1 = $x1*$adjust;
					foreach ($y1s as $y1) {
						$y1 = $y1*$adjust;
						foreach ($ws as $w) {
							$w = $w*$adjust;
							$scale = 100/$w;
							foreach ($hs as $h) {
							$h = $h*$adjust;
							$result = HighSlide_Plugin::crophandle($thumbroot,$imgroot,$w,$h,$x1,$y1,$scale);
							}
						}
					}
				}
				if (chmod($result,0777) !== false) {
					$this->response->throwJson(array(
							'bytes'=>number_format(ceil(filesize($result)/1024)).' Kb'
							));
				}
			}
		}
        $this->response->throwJson(false);
	}

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
	public function action()
	{
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is('do=insert'))->insertgallery();
		$this->on($this->request->is('do=update'))->updategallery();
		$this->on($this->request->is('do=delete'))->deletegallery();
		$this->on($this->request->is('do=sort'))->sortgallery();
		$this->on($this->request->is('do=upload'))->uploadimage();
		$this->on($this->request->is('do=remove'))->removeimage();
		$this->on($this->request->is('do=crop'))->cropthumbnail();
		$this->response->redirect($this->options->adminUrl);
	}
}