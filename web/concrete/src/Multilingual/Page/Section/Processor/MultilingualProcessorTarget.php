<?php

namespace Concrete\Core\Multilingual\Page\Section\Processor;

use Concrete\Core\Foundation\Processor\TargetInterface;
use Concrete\Core\Multilingual\Page\Section\Section;

defined('C5_EXECUTE') or die("Access Denied.");

class MultilingualProcessorTarget implements TargetInterface
{
    protected $section;

    public function __construct(Section $section)
    {
        $this->section = $section;
    }

    /**
     * @return Section
     */
    public function getSection()
    {
        return $this->section;
    }

    public function getItems()
    {
        $pages = $this->section->populateRecursivePages(array(), array(
            'cID' => $this->section->getCollectionID()),
            $this->section->getCollectionParentID(), 0, false
        );
        return $pages;
    }

}
