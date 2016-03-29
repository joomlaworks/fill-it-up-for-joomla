<?php
/**
 * @version    1.x
 * @package    Fill It Up
 * @author     JoomlaWorks http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    http://www.joomlaworks.net/license
 */

// No direct access
defined('_JEXEC') or die ;

class plgFillItUpContent extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_'.$this->_type.'_'.$this->_name.'.sys');
	}

	public function generateCategory($response, $generator, $categoryName, $params, $definitions)
	{

		// Set some variables
		$user = JFactory::getUser();
		$date = JFactory::getDate();

		// Set the category variables
		$category = JTable::getInstance('Category', 'JTable');
		$category->title = $categoryName;
		$category->alias = $category->title;
		$category->parent_id = 1;
		$category->published = 1;
		$category->access = 1;
		$category->language = '*';
		$category->description = '<p>'.implode('</p><p>', $generator->paragraphs(rand(1, 4))).'</p>';
		$category->created_user_id = $user->id;
		if($params->articlesAuthor == 'random')
		{
			$category->created_user_id = $this->getRandomUser();
		}
		$category->created_time = $date->toSql();
		$category->extension = 'com_content';
		$category->setLocation($category->parent_id, 'last-child');
		$category->check();
		// Check for duplicate alias
		$table = JTable::getInstance('Category', 'JTable');
		if ($table->load(array('alias' => $category->alias, 'parent_id' => $category->parent_id, 'extension' => $category->extension)) && $category->id == 0)
		{
			$category->alias .= '_'.uniqid();
		}
		// Quick fix to avoid breaking JSON due to tags implementation under Joomla! 3.1. @TODO Implement tags generation for Joomla! 3.1
		@$category->store();
		$category->rebuildPath($category->id);
		$category->rebuild($category->id, $category->lft, $category->level, $category->path);
		$response->category = $category->id;
	}

	public function generateRow($response, $generator, $categories, $params)
	{

		// Set some variables
		$user = JFactory::getUser();
		$date = JFactory::getDate();

		// Set the item variables
		$article = JTable::getInstance('Content', 'JTable');
		$article->title = $generator->sentence(rand(3, 6));
		$article->title = ucfirst($article->title);
		$article->title = str_replace(array('.', ','), '', $article->title);
		$article->alias = $article->title;
		$article->catid = $categories[array_rand($categories)];
		$article->state = 1;
		$article->introtext = '<p>'.implode('</p><p>', $generator->paragraphs(rand(1, 4))).'</p>';
		$article->fulltext = '<p>'.implode('</p><p>', $generator->paragraphs(rand(4, 8))).'</p>';
		$article->images = $this->getRandomImages($article->catid, $article->title, $generator, $params);
		$article->created = $date->toSql();
		$article->publish_up = $article->created;
		$article->created_by = $user->id;
		if($params->articlesAuthor == 'random')
		{
			$article->created_by = $this->getRandomUser();
		}
		$article->access = 1;
		$article->reorder('catid = '.(int)$article->catid.' AND state >= 0');
		$article->hits = 0;
		$article->language = '*';
		$article->metakey = $this->getRandomKeywords($article->catid);
		$media = $this->getRandomMedia($article->catid);
		$gallery = $this->getRandomGallery($article->catid);
		if ($media || $gallery)
		{
			//$article->fulltext .= '<dl class="fillItUpTabs tabs">';
			if ($media)
			{
				//$article->fulltext .= '<dt class="fillItUpTabTitle"><span><h3><a href="javascript:void(0);">'.JText::_('PLG_FILLITUP_CONTENT_MEDIA').'</a></h3></span></dt><dd class="fillItUpTabDescription"><div class="fillItUpMedia">'.$media.'</div></dd>';
				$article->fulltext .= '<div class="fillItUpMedia">'.$media.'</div>';
				
			}

			if ($gallery)
			{
				//$article->fulltext .= '<dt class="fillItUpTabTitle"><span><h3><a href="javascript:void(0);">'.JText::_('PLG_FILLITUP_CONTENT_GALLERY').'</a></h3></span></dt><dd class="fillItUpTabDescription"><div class="fillItUpGallery">'.$gallery.'</div></dd>';
				$article->fulltext .= '<div class="fillItUpGallery">'.$gallery.'</div>';
			
			}
			//$article->fulltext .= '</dl>';
		}
		$article->check();
		// Quick fix to avoid breaking JSON due to tags implementation under Joomla! 3.1. @TODO Implement tags generation for Joomla! 3.1
		@$article->store();
	}

	public function createCategoryImage($catid, $params, $generator)
	{
		$imagesFolder = JRequest::getCmd('imagesFolder');
		$sourceFolder = JPATH_SITE.'/media/jw_fillitup/'.$imagesFolder.'/'.$catid;
		$targetFolder = JPATH_SITE.'/images/jw_fillitup/com_content/'.$catid;
		if (!JFolder::exists($sourceFolder))
		{
			return null;
		}
		if (!JFolder::exists($targetFolder))
		{
			JFolder::create($targetFolder);
		}
		$files = JFolder::files($sourceFolder, '.jpg|.jpeg|.gif|.png');
		$random = array_rand($files);

		JLoader::register('Upload', JPATH_ADMINISTRATOR.'/components/com_jw_fillitup/lib/class.upload.php');
		$image = $sourceFolder.'/'.$files[$random];

		$catImage = basename(JFile::stripExt($files[$random])).'_'.$params->catImageWidth.'.jpg';

		if (!JFile::exists($targetFolder.'/'.$catImage))
		{
			$handle = new Upload($image);
			$handle->allowed = array('image/*');
			$handle->file_new_name_body = JFile::stripExt($catImage);
			$handle->image_resize = true;
			$handle->image_ratio_y = true;
			$handle->image_convert = 'jpg';
			$handle->jpeg_quality = 100;
			$handle->file_auto_rename = true;
			$handle->file_overwrite = false;
			$handle->image_x = $params->catImageWidth;
			$handle->Process($targetFolder);
		}

		$imageValue = 'images/jw_fillitup/com_content/'.$catid.'/'.$catImage;
		
		$category = JTable::getInstance('Category', 'JTable');
		$category->load($catid);
		
		if(is_string($category->params))
		{
			$catParams = new JRegistry($category->params);
		}
		else {
			$catParams = $category->params;
		}
		
		$catParams->set('image', $imageValue);
		
		$category->params = $catParams->toString();
		@$category->store();
		
	}

	protected function getRandomImages($catid, $title, $generator, $params)
	{
		$imagesFolder = JRequest::getCmd('imagesFolder');
		$sourceFolder = JPATH_SITE.'/media/jw_fillitup/'.$imagesFolder.'/'.$catid;
		$targetFolder = JPATH_SITE.'/images/jw_fillitup/com_content/'.$catid;
		if (!JFolder::exists($sourceFolder))
		{
			return null;
		}
		if (!JFolder::exists($targetFolder))
		{
			JFolder::create($targetFolder);
		}
		$files = JFolder::files($sourceFolder, '.jpg|.jpeg|.gif|.png');
		$random = array_rand($files);

		JLoader::register('Upload', JPATH_ADMINISTRATOR.'/components/com_jw_fillitup/lib/class.upload.php');
		$image = $sourceFolder.'/'.$files[$random];

		$introImage = basename(JFile::stripExt($files[$random])).'_'.$params->introtextImage.'.jpg';
		$fullImage = basename(JFile::stripExt($files[$random])).'_'.$params->fulltextImage.'.jpg';

		if (!JFile::exists($targetFolder.'/'.$introImage))
		{
			$handle = new Upload($image);
			$handle->allowed = array('image/*');
			$handle->file_new_name_body = JFile::stripExt($introImage);
			$handle->image_resize = true;
			$handle->image_ratio_y = true;
			$handle->image_convert = 'jpg';
			$handle->jpeg_quality = 100;
			$handle->file_auto_rename = true;
			$handle->file_overwrite = false;
			$handle->image_x = $params->introtextImage;
			$handle->Process($targetFolder);
		}

		if (!JFile::exists($targetFolder.'/'.$fullImage))
		{
			$handle = new Upload($image);
			$handle->allowed = array('image/*');
			$handle->file_new_name_body = JFile::stripExt($fullImage);
			$handle->image_resize = true;
			$handle->image_ratio_y = true;
			$handle->image_convert = 'jpg';
			$handle->jpeg_quality = 100;
			$handle->file_auto_rename = true;
			$handle->file_overwrite = false;
			$handle->image_x = $params->fulltextImage;
			$handle->Process($targetFolder);
		}

		$caption = $generator->sentence(rand(3, 6));
		$caption = ucfirst($caption);
		$caption = str_replace(array('.', ','), '', $caption);
		$images = new stdClass;
		$images->image_intro = 'images/jw_fillitup/com_content/'.$catid.'/'.$introImage;
		$images->float_intro = '';
		$images->image_intro_alt = $title;
		$images->image_intro_caption = $caption;
		$images->image_fulltext = 'images/jw_fillitup/com_content/'.$catid.'/'.$fullImage;
		$images->float_fulltext = '';
		$images->image_fulltext_alt = $title;
		$images->image_fulltext_caption = $caption;
		return json_encode($images);
	}

	protected function getRandomMedia($catid)
	{
		$media = NULL;
		$definitions = json_decode(JRequest::getVar('definitions'));
		foreach ($definitions as $definition)
		{
			if ($definition->id == $catid)
			{
				$random = $definition->media[array_rand($definition->media)];
				if (JString::strpos($random, 'vimeo.com'))
				{
					$media = '{vimeo}'.$random.'{/vimeo}';
				}
				else
				{
					$media = '{youtube}'.$random.'{/youtube}';
				}
			}
		}
		return $media;
	}

	protected function getRandomGallery($catid)
	{
		$gallery = NULL;
		$definitions = json_decode(JRequest::getVar('definitions'));
		foreach ($definitions as $definition)
		{
			if ($definition->id == $catid)
			{
				$random = $definition->galleries[array_rand($definition->galleries)];
				$gallery = '{gallery}'.$random.'{/gallery}';
			}
		}
		return $gallery;
	}

	protected function getRandomKeywords($catid)
	{
		$keywords = null;
		$definitions = json_decode(JRequest::getVar('definitions'));
		foreach ($definitions as $definition)
		{
			if ($definition->id == $catid)
			{
				$random = $definition->galleries[array_rand($definition->galleries)];
				$gallery = '{gallery}'.$random.'{/gallery}';

				$numOfTags = count($definition->tags);
				$max = (count($definition->tags) < 4) ? count($definition->tags) : 4;
				$randoms = array_rand($definition->tags, rand(2, $max));
				$tags = array();
				foreach ($randoms as $random)
				{
					$tags[] = $definition->tags[$random];
				}
				$keywords = implode(', ', $tags);
			}
		}
		return $keywords;
	}

	protected function getRandomUser()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))->from($db->quoteName('#__users'))->where($db->quoteName('block').' = 0')->order('RAND()');
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	}
}
