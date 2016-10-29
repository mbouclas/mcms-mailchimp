<?php

namespace Mcms\Mailchimp\Service;

use Illuminate\Support\Collection;
use Mcms\Mailchimp\Exceptions\InvalidMailchimpList;

/**
 * Class MailchimpListCollection
 * @package Mcms\Mailchimp\Service
 */
class MailchimpListCollection extends Collection
{
    /** @var string */
    public $defaultListName = '';

    /**
     * @param array $config
     *
     * @return static
     */
    public static function createFromConfig(array $config)
    {
        return self::createCollection($config['lists'], $config['defaultListName']);
    }

    /**
     * @param $listID
     * @param string $listName
     * @return MailchimpListCollection
     */
    public static function createFromString($listID, $listName = 'subscribers')
    {
        $list = [];
        $list[$listName] = ['id' => $listID];
        return self::createCollection($list, $listName);
    }

    /**
     * @param array $lists
     * @param $defaultListName
     * @return MailchimpListCollection
     */
    private static function createCollection(array $lists, $defaultListName){
        $collection = new static();

        foreach ($lists as $name => $listProperties) {
            $collection->push(new MaichimpList($name, $listProperties));
        }

        $collection->defaultListName = (isset($defaultListName)) ?$defaultListName : 'subscribers';

        return $collection;
    }


    /**
     * @param string $name
     * @return MaichimpList
     * @throws InvalidMailchimpList
     */
    public function findByName($name)
    {
        if ((string) $name === '') {
            return $this->getDefault();
        }

        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $name) {
                return $newsletterList;
            }
        }

        throw InvalidMailchimpList::noListWithName($name);
    }


    /**
     * @return MaichimpList
     * @throws InvalidMailchimpList
     */
    public function getDefault()
    {
        foreach ($this->items as $newsletterList) {
            if ($newsletterList->getName() === $this->defaultListName) {
                return $newsletterList;
            }
        }

        throw InvalidMailchimpList::defaultListDoesNotExist($this->defaultListName);
    }
}
