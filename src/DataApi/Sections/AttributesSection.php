<?php

namespace DataBreakers\DataApi\Sections;


use DataBreakers\DataApi\DataType;
use DataBreakers\DataApi\Exceptions\InvalidArgumentException;
use DataBreakers\DataApi\Exceptions\RequestFailedException;
use DataBreakers\DataApi\MetaType;
use DataBreakers\DataApi\Utils\Restriction;


class AttributesSection extends Section
{

	const ADD_USERS_ATTRIBUTE_URL = '/{accountId}/attribute/users';
	const ADD_ITEMS_ATTRIBUTE_URL = '/{accountId}/attribute/items';

	const GET_USERS_ATTRIBUTES_URL = '/{accountId}/attributes/users';
	const GET_ITEMS_ATTRIBUTES_URL = '/{accountId}/attributes/items';

	const DELETE_USERS_ATTRIBUTE_URL = '/{accountId}/attributes/users/{attributeName}';
	const DELETE_ITEMS_ATTRIBUTE_URL = '/{accountId}/attributes/items/{attributeName}';

	const ATTRIBUTE_NAME_PARAMETER = 'attributeName';
	const NAME_PARAMETER = 'name';
	const DATA_TYPE_PARAMETER = 'dataType';
	const DESCRIPTION_PARAMETER = 'description';
	const LANGUAGE_PARAMETER = 'language';
	const META_TYPE_PARAMETER = 'metaType';


	/**
	 * @param string $name
	 * @param string $dataType
	 * @param string|NULL $language
	 * @param string|NULL $metaType
	 * @return NULL
	 * @throws InvalidArgumentException when given name is empty
	 * @throws InvalidArgumentException when given name doesn't match required pattern
	 * @throws InvalidArgumentException when given data type is empty
	 * @throws InvalidArgumentException when given data type isn't known data type
	 * @throws InvalidArgumentException when given meta type isn't known meta type
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function addUsersAttribute($name, $dataType, $language = NULL, $metaType = NULL)
	{
		return $this->addAttribute(self::ADD_USERS_ATTRIBUTE_URL, $name, $dataType, $language, $metaType);
	}

	/**
	 * @param string $name
	 * @param string $dataType
	 * @param string|NULL $language
	 * @param string|NULL $metaType
	 * @return NULL
	 * @throws InvalidArgumentException when given name is empty
	 * @throws InvalidArgumentException when given name doesn't match required pattern
	 * @throws InvalidArgumentException when given data type is empty
	 * @throws InvalidArgumentException when given data type isn't known data type
	 * @throws InvalidArgumentException when given meta type isn't known meta type
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function addItemsAttribute($name, $dataType, $language = NULL, $metaType = NULL)
	{
		return $this->addAttribute(self::ADD_ITEMS_ATTRIBUTE_URL, $name, $dataType, $language, $metaType);
	}

	/**
	 * @return array
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function getUsersAttributes()
	{
		return $this->performGet(self::GET_USERS_ATTRIBUTES_URL);
	}

	/**
	 * @return array
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function getItemsAttributes()
	{
		return $this->performGet(self::GET_ITEMS_ATTRIBUTES_URL);
	}

	/**
	 * @param string $attributeName
	 * @return NULL
	 * @throws InvalidArgumentException when given attribute name is empty
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function deleteUsersAttribute($attributeName)
	{
		return $this->deleteAttribute(self::DELETE_USERS_ATTRIBUTE_URL, $attributeName);
	}

	/**
	 * @param string $attributeName
	 * @return NULL
	 * @throws InvalidArgumentException when given attribute name is empty
	 * @throws RequestFailedException when request failed for some reason
	 */
	public function deleteItemsAttribute($attributeName)
	{
		return $this->deleteAttribute(self::DELETE_ITEMS_ATTRIBUTE_URL, $attributeName);
	}

	/**
	 * @param string $path
	 * @param string $name
	 * @param string $dataType
	 * @param string|NULL $language
	 * @param string|NULL $metaType
	 * @return NULL
	 * @throws InvalidArgumentException when given name is empty
	 * @throws InvalidArgumentException when given name doesn't match required pattern
	 * @throws InvalidArgumentException when given data type is empty
	 * @throws InvalidArgumentException when given data type isn't known data type
	 * @throws InvalidArgumentException when given meta type isn't known meta type
	 * @throws RequestFailedException when request failed for some reason
	 */
	private function addAttribute($path, $name, $dataType, $language = NULL, $metaType = NULL)
	{
		$this->validateAddAttributeArguments($name, $dataType, $metaType);
		$content = $this->constructAddAttributeContent($name, $dataType, $language, $metaType);
		$restrictions = new Restriction([], $content);
		return $this->performPost($path, $restrictions);
	}

	/**
	 * @param string $name
	 * @param string $dataType
	 * @param string|NULL $metaType
	 * @return void
	 * @throws InvalidArgumentException when given name is empty
	 * @throws InvalidArgumentException when given name doesn't match required pattern
	 * @throws InvalidArgumentException when given data type is empty
	 * @throws InvalidArgumentException when given data type isn't known data type
	 * @throws InvalidArgumentException when given meta type isn't known meta type
	 */
	private function validateAddAttributeArguments($name, $dataType, $metaType = NULL)
	{
		if ($name == '') {
			throw new InvalidArgumentException("Attribute name can't be empty value.");
		}
		if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $name)) {
			throw new InvalidArgumentException("Attribute name can only contain letters, numbers and _ and has to start with a letter.");
		}
		if ($dataType == '') {
			throw new InvalidArgumentException("Data type can't be empty value.");
		}
		if (!DataType::isValidDataType($dataType)) {
			throw new InvalidArgumentException("Given data type isn't known data type.");
		}
		if ($metaType !== NULL && !MetaType::isValidMetaType($metaType)) {
			throw new InvalidArgumentException("Given meta type isn't known meta type.");
		}
	}

	/**
	 * @param string $name
	 * @param string $dataType
	 * @param string|NULL $language
	 * @param string|NULL $metaType
	 * @return array
	 */
	private function constructAddAttributeContent($name, $dataType, $language = NULL, $metaType = NULL)
	{
		$content = [
			self::NAME_PARAMETER => $name,
			self::DATA_TYPE_PARAMETER => $dataType
		];
		$description = [];
		if ($language !== NULL) {
			$description[self::LANGUAGE_PARAMETER] = $language;
		}
		if ($metaType !== NULL) {
			$description[self::META_TYPE_PARAMETER] = $metaType;
		}
		if ($description !== []) {
			$content[self::DESCRIPTION_PARAMETER] = $description;
		}
		return $content;
	}

	/**
	 * @param string $path
	 * @param string $attributeName
	 * @return array|NULL
	 * @throws InvalidArgumentException when given attribute name is empty
	 * @throws RequestFailedException when request failed for some reason
	 */
	private function deleteAttribute($path, $attributeName)
	{
		if ($attributeName == '') {
			throw new InvalidArgumentException("Attribute name can't be empty string.");
		}
		$restrictions = new Restriction([self::ATTRIBUTE_NAME_PARAMETER => $attributeName]);
		return $this->performDelete($path, $restrictions);
	}

}