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

class plgFillItUpK2 extends JPlugin
{

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_'.$this->_type.'_'.$this->_name.'.sys');

		// Get K2 version
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('name').' = '.$db->quote('com_k2'));
		$db->setQuery($query);
		$manifest = json_decode($db->loadResult());
		$this->version = $manifest->version;
	}

	public function generateCategory($response, $generator, $category, $params, $definitions)
	{
		$users = (int) $params->users;
		if($response->offset == 1 && $users)
		{
			for ($i = 0; $i < $users; $i++)
			{
				$data = array();
				$data['block'] = 0;
				$data['name'] = $generator->name;
				$data['username'] = $generator->userName;
				$data['email'] = $generator->email;
				$data['password'] = '';
				$data['password2'] = $data['password'];
				$groups = $params->groups;
				if (!is_array($groups) || !count($groups))
				{
					$userParams = JComponentHelper::getParams('com_users');
					$groups = array($userParams->get('new_usertype', 2));
				}
				$data['groups'] = $groups;
				$row = JUser::getInstance(0);
				$row->bind($data);
				$row->save();
			}
		}

		if (version_compare($this->version, '3.0.0', 'ge'))
		{
			$data = array();
			$data['id'] = '';
			$data['title'] = $category;
			$data['alias'] = '';
			$data['state'] = 1;
			$data['access'] = 1;
			$data['description'] = '<p>'.implode('</p><p>', $generator->paragraphs(rand(2, 4))).'</p>';
			$data['parent_id'] = 1;
			$data['language'] = '*';
			$categoryParams = new JRegistry($this->getDefaultParams('categories'));
			$categoryParams->set('imageSizes', $params->images);
			$data['params'] = $categoryParams->toString();
			if($params->itemsAuthor == 'random')
			{
				$data['created_by'] = $this->getRandomUser();
			}
			$model = K2Model::getInstance('Categories');
			$model->setState('data', $data);
			$model->save();
			$response->category = $model->getState('id');
		}
		else
		{
			// Import K2 classes
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

			// Set the category variables
			$K2Category = JTable::getInstance('K2Category', 'Table');
			$K2Category->name = $category;
			$K2Category->alias = $K2Category->name;
			$K2Category->description = $generator->text(100);
			$K2Category->description = ucfirst($K2Category->description);
			$K2Category->published = 1;
			$K2Category->access = 1;
			$K2Category->trash = 0;
			$K2Category->parent = 0;
			$K2Category->ordering = $K2Category->getNextOrder("parent = ".$K2Category->parent);
			$K2Category->params = $this->getDefaultParams('category');
			$K2Category->language = '*';
			$K2Category->check();
			$K2Category->store();
			$response->category = $K2Category->id;
		}

		// Generate category tags
		foreach ($definitions as $definition)
		{
			if ($definition->name == $category)
			{
				$db = JFactory::getDbo();
				foreach ($definition->tags as $tag)
				{
					$query = $db->getQuery(true);
					$query->select($db->quoteName('id'))->from($db->quoteName('#__k2_tags'))->where($db->quoteName('name').' = '.$db->quote($tag));
					$db->setQuery($query);
					$tagId = $db->loadResult();
					if (!$tagId)
					{
						if (version_compare($this->version, '3.0.0', 'ge'))
						{
							$data = array();
							$data['id'] = '';
							$data['name'] = $tag;
							$data['alias'] = '';
							$data['state'] = 1;
							$model = K2Model::getInstance('Tags');
							$model->setState('data', $data);
							$model->save();
							$tagId = $model->getState('id');
						}
						else
						{
							$K2Tag = JTable::getInstance('K2Tag', 'Table');
							$K2Tag->name = $tag;
							$K2Tag->published = 1;
							$K2Tag->check();
							$K2Tag->store();
							$tagId = $K2Tag->id;
						}
					}
					$definition->tagIDs[] = $tagId;
				}
			}
		}

	}

	public function generateRow($response, $generator, $categories, $params)
	{

		if (version_compare($this->version, '3.0.0', 'ge'))
		{
			$data = array();
			$data['id'] = '';
			$data['title'] = ucfirst($generator->sentence(rand(3, 6)));
			$data['title'] = str_replace(array('.', ','), '', $data['title']);
			$data['alias'] = '';
			$data['state'] = 1;
			$data['access'] = 1;
			$data['catid'] = $categories[array_rand($categories)];
			$data['introtext'] = '<p>'.implode('</p><p>', $generator->paragraphs(rand(1, 4))).'</p>';
			$data['fulltext'] = '<p>'.implode('</p><p>', $generator->paragraphs(rand(4, 8))).'</p>';
			$data['params'] = $this->getDefaultParams('items');
			$data['language'] = '*';

			// Image
			$caption = ucfirst($generator->sentence(rand(3, 6)));
			$capton = str_replace(array('.', ','), '', $caption);
			$imagePath = str_replace(JPATH_SITE.'/', '', $this->getRandomImage($data['catid']));
			if($imagePath)
			{
				$image = K2HelperImages::add('item', null, $imagePath);
				$data['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => $capton, 'credits' => 'Generated by FillItUp');
			}

			// Media
			$caption = ucfirst($generator->sentence(rand(3, 6)));
			$capton = str_replace(array('.', ','), '', $caption);
			$tag = $this->getRandomMedia($data['catid']);
			preg_match("#}(.*?){/#s", $tag, $matches);
			$videoId = $matches[1];
			$tag = substr($tag, 1);
			$provider = substr($tag, 0, strpos($tag, '}'));
			$media = array();
			$video = array();
			$video['url'] = '';
			$video['provider'] = $provider;
			$video['id'] = $videoId;
			$video['embed'] = '';
			$video['caption'] = $caption;
			$video['credits'] = 'Generated by FillItUp';
			$video['upload'] = '';
			$video['remove'] = 0;
			$media[] = $video;
			$data['media'] = $media;

			// Author
			if($params->itemsAuthor == 'random')
			{
				$data['created_by'] = $this->getRandomUser();
			}

			// Gallery
			$galleries = array();
			$gallery = array();
			preg_match("#}(.*?){/#s", $this->getRandomGallery($data['catid']), $matches);
			$value = $matches[1];
			if (strpos($value, 'flickr.com') !== false)
			{
				$gallery['url'] = $value;
				$gallery['upload'] = '';
			}
			$galleries[] = $gallery;
			$data['gallery'] = $galleries;

			// Tags
			$definitions = json_decode(JRequest::getVar('definitions'));
			foreach ($definitions as $definition)
			{
				if ($definition->id == $data['catid'])
				{
					$numOfTags = count($definition->tags);
					$max = $numOfTags < 4 ? $numOfTags : 4;
					$randoms = array_rand($definition->tags, rand(2, $max));
					$tags = array();
					foreach ($randoms as $random);
					{
						$tags[] = $definition->tags[$random];
					}
					$data['tags'] = implode(',', $tags);
				}
			}
			$model = K2Model::getInstance('Items');
			$model->setState('data', $data);
			$model->save();
			$itemId = $model->getState('id');

			// Comments
			$numOfCommentsPerItem = (int)$params->numOfCommentsPerItem;
			if ($numOfCommentsPerItem)
			{

				// Generate users pool
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->select($db->quoteName('name'));
				$query->select($db->quoteName('email'));
				$query->from($db->quoteName('#__users'));
				$query->where($db->quoteName('block').' = 0');
				$query->order('RAND()');
				$db->setQuery($query, 0, 20);
				$commenters = $db->loadObjectList();
				$guest = new stdClass;
				$guest->id = 0;
				$guest->name = 'John Smith';
				$guest->email = 'johnsmith@127.0.0.1';
				$commenters[] = $guest;
				$numOfCommenters = count($commenters);

				$num = rand(0, $numOfCommentsPerItem);
				for ($i = 0; $i < $num; $i++)
				{
					$commenter = $commenters[rand(0, ($numOfCommenters - 1))];
					K2Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
					$table = K2Table::getInstance('Comments', 'K2Table');
					$table->itemId = $itemId;
					$table->userId = $commenter->id;
					$table->name = $commenter->name;
					$table->date = JFactory::getDate()->toSql();
					$table->email = $commenter->email;
					$table->url = '';
					$table->ip = $_SERVER['REMOTE_ADDR'];
					$table->hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
					$table->text = $generator->text(rand(10, 50));
					$table->state = 1;
					$table->store();

					$statistics = K2Model::getInstance('Statistics', 'K2Model');
					$statistics->increaseItemCommentsCounter($table->itemId);
					if ($table->userId > 0)
					{
						$statistics->increaseUserCommentsCounter($table->userId);
					}

				}

			}

		}
		else
		{
			// Import K2 classes
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');

			// Set some variables
			$user = JFactory::getUser();
			$date = JFactory::getDate();

			// Set the item variables
			$K2Item = JTable::getInstance('K2Item', 'Table');
			$K2Item->title = $generator->sentence(rand(3, 6));
			$K2Item->title = ucfirst($K2Item->title);
			$K2Item->title = str_replace(array('.', ','), '', $K2Item->title);
			$K2Item->alias = $K2Item->title;
			$K2Item->catid = $categories[array_rand($categories)];
			$K2Item->trash = 0;
			$K2Item->published = 1;
			$K2Item->introtext = '<p>'.implode('</p><p>', $generator->paragraphs(rand(1, 4))).'</p>';
			$K2Item->fulltext = '<p>'.implode('</p><p>', $generator->paragraphs(rand(4, 8))).'</p>';
			$K2Item->created = $date->toSql();
			$K2Item->publish_up = $K2Item->created;
			$K2Item->created_by = $user->id;
			if($params->itemsAuthor == 'random')
			{
				$K2Item->created_by = $this->getRandomUser();
			}
			$K2Item->access = 1;
			$K2Item->ordering = $K2Item->getNextOrder("catid = ".$K2Item->catid);
			$K2Item->hits = 0;
			$K2Item->params = $this->getDefaultParams('item');
			$K2Item->language = '*';
			$K2Item->image_caption = $generator->sentence(rand(3, 6));
			$K2Item->image_caption = ucfirst($K2Item->image_caption);
			$K2Item->image_caption = str_replace(array('.', ','), '', $K2Item->image_caption);
			$K2Item->image_credits = 'Generated by FillItUp';
			$K2Item->video_caption = $generator->sentence(rand(3, 6));
			$K2Item->video_caption = ucfirst($K2Item->video_caption);
			$K2Item->video_caption = str_replace(array('.', ','), '', $K2Item->video_caption);
			$K2Item->video_credits = 'Generated by FillItUp';
			$K2Item->video = $this->getRandomMedia($K2Item->catid);
			$K2Item->gallery = $this->getRandomGallery($K2Item->catid);
			$K2Item->check();
			$K2Item->store();

			// Generate images
			$this->createItemImages($K2Item->id, $K2Item->catid, $params);

			// Tag item
			$this->tagItem($K2Item->id, $K2Item->catid);

			// Comments
			$numOfCommentsPerItem = (int)$params->numOfCommentsPerItem;
			if ($numOfCommentsPerItem)
			{
				// Generate users pool
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->select($db->quoteName('name'));
				$query->select($db->quoteName('email'));
				$query->from($db->quoteName('#__users'));
				$query->where($db->quoteName('block').' = 0');
				$query->order('RAND()');
				$db->setQuery($query, 0, 20);
				$commenters = $db->loadObjectList();
				$guest = new stdClass;
				$guest->id = 0;
				$guest->name = 'John Smith';
				$guest->email = 'johnsmith@127.0.0.1';
				$commenters[] = $guest;
				$numOfCommenters = count($commenters);

				$num = rand(0, $numOfCommentsPerItem);
				for ($i = 0; $i < $num; $i++)
				{
					$commenter = $commenters[rand(0, ($numOfCommenters - 1))];
					K2Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
					$table = JTable::getInstance('K2Comment', 'Table');
					$table->itemID = $K2Item->id;
					$table->userID = $commenter->id;
					$table->userName = $commenter->name;
					$table->commentDate = JFactory::getDate()->toSql();
					$table->commentText = $generator->text(rand(10, 50));
					$table->commentEmail = $commenter->email;
					$table->commentURL = '';
					$table->published = 1;
					$table->store();
				}

			}
		}

	}

	protected function getDefaultParams($type)
	{
		// Import JForm
		jimport('joomla.form.form');

		// Determine form name and path
		$formName = 'K2'.ucfirst($type).'Form';
		$formPath = JPATH_ADMINISTRATOR.'/components/com_k2/models/'.$type.'.xml';

		// Get form
		$form = JForm::getInstance($formName, $formPath);

		// Build params from XML
		$params = new JRegistry('');
		foreach ($form->getFieldsets() as $fieldset)
		{
			foreach ($form->getFieldset($fieldset->name) as $field)
			{
				$params->set((string)$field->fieldname, (string)$field->value);
			}
		}
		return $params->toString();
	}

	public function createCategoryImage($catid, $params, $generator)
	{
		$image = $this->getRandomImage($catid);
		if (!$image)
		{
			return;
		}
		if (version_compare($this->version, '3.0.0', 'ge'))
		{
			$config = JComponentHelper::getParams('com_k2');
			$config->set('catImageWidth', $params->catImageWidth);
			$data = array();
			$data['id'] = $catid;
			$caption = ucfirst($generator->sentence(rand(3, 6)));
			$capton = str_replace(array('.', ','), '', $caption);
			$imagePath = str_replace(JPATH_SITE.'/', '', $this->getRandomImage($catid));
			$image = K2HelperImages::add('category', null, $imagePath);
			$data['image'] = array('id' => '', 'temp' => $image->temp, 'path' => '', 'remove' => 0, 'caption' => $capton, 'credits' => 'Generated by FillItUp');
			$model = K2Model::getInstance('Categories');
			$model->setState('data', $data);
			$model->save();

		}
		else
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
			$K2Category = JTable::getInstance('K2Category', 'Table');
			$K2Category->load($catid);
			if (!$K2Category->id)
			{
				return;
			}
			JLoader::register('Upload', JPATH_ADMINISTRATOR.'/components/com_k2/lib/class.upload.php');
			$savepath = JPATH_SITE.'/media/k2/categories';
			$handle = new Upload($image);
			$handle->allowed = array('image/*');
			$handle->image_convert = 'jpg';
			$handle->file_auto_rename = false;
			$handle->jpeg_quality = 100;
			$handle->file_overwrite = true;
			$handle->file_new_name_body = $K2Category->id;
			$handle->image_resize = true;
			$handle->image_ratio_y = true;
			$handle->image_x = $params->catImageWidth;
			$handle->Process($savepath);
			$K2Category->image = $handle->file_dst_name;
			$K2Category->store();
		}

	}

	protected function createItemImages($id, $catid, $params)
	{
		JLoader::register('Upload', JPATH_ADMINISTRATOR.'/components/com_k2/lib/class.upload.php');
		$image = $this->getRandomImage($catid);

		if (!$image)
		{
			return;
		}

		$handle = new Upload($image);
		$handle->allowed = array('image/*');

		//Original image
		$savepath = JPATH_SITE.'/media/k2/items/src';
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = md5("Image".$id);
		$handle->Process($savepath);

		$filename = $handle->file_dst_name_body;
		$savepath = JPATH_SITE.'/media/k2/items/cache';

		//XLarge image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_XL';
		$handle->image_x = $params->xlarge;
		$handle->Process($savepath);

		//Large image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_L';
		$handle->image_x = $params->large;
		$handle->Process($savepath);

		//Medium image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_M';
		$handle->image_x = $params->medium;
		$handle->Process($savepath);

		//Small image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_S';
		$handle->image_x = $params->small;
		$handle->Process($savepath);

		//XSmall image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_XS';
		$handle->image_x = $params->xsmall;
		$handle->Process($savepath);

		//Generic image
		$handle->image_resize = true;
		$handle->image_ratio_y = true;
		$handle->image_convert = 'jpg';
		$handle->jpeg_quality = 100;
		$handle->file_auto_rename = false;
		$handle->file_overwrite = true;
		$handle->file_new_name_body = $filename.'_Generic';
		$handle->image_x = $params->generic;
		$handle->Process($savepath);
	}

	protected function getRandomImage($catid)
	{
		$imagesFolder = JRequest::getCmd('imagesFolder');
		$folder = JPATH_SITE.'/media/jw_fillitup/'.$imagesFolder.'/'.$catid;
		if (JFolder::exists($folder))
		{
			$files = JFolder::files($folder, '.jpg|.jpeg|.gif|.png');
			if (count($files) > 0)
			{
				return $folder.'/'.$files[array_rand($files)];
			}
		}
		return false;
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

	protected function getRandomUser()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'))->from($db->quoteName('#__users'))->where($db->quoteName('block').' = 0')->order('RAND()');
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	}

	protected function tagItem($id, $catid)
	{
		$db = JFactory::getDbo();
		$definitions = json_decode(JRequest::getVar('definitions'));
		foreach ($definitions as $definition)
		{
			if ($definition->id == $catid)
			{
				$numOfTags = count($definition->tagIDs);
				$max = (count($definition->tagIDs) < 4) ? count($definition->tagIDs) : 4;
				$randoms = array_rand($definition->tagIDs, rand(2, $max));
				foreach ($randoms as $random)
				{
					$query = $db->getQuery(true);
					$query->insert($db->quoteName('#__k2_tags_xref'));
					if (version_compare($this->version, '3.0.0', 'ge'))
					{
						$query->columns($db->quoteName('tagId').','.$db->quoteName('itemId'))->values((int)$definition->tagIDs[$random].','.(int)$id);
					}
					else
					{
						$query->columns($db->quoteName('id').','.$db->quoteName('tagID').','.$db->quoteName('itemID'))->values('NULL,'.(int)$definition->tagIDs[$random].','.(int)$id);
					}
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		return true;
	}

}
