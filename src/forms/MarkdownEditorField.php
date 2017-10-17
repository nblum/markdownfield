<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/18/15
 * Time: 11:12 AM
 * To change this template use File | Settings | File Templates.
 */
namespace SilverStripers\markdown\forms;

use SilverStripe\Assets\Shortcodes\ImageShortcodeProvider;
use SilverStripe\Forms\TextareaField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use Exception;
use SilverStripe\View\Parsers\HTMLValue;
use SilverStripe\Forms\HTMLEditor\HTMLEditorSanitiser;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;

class MarkdownEditorField extends TextareaField
{

    private static $allowed_actions = array(
        'preview'
    );

    protected $rows = 30;

    public function Field($properties = array())
    {
        return parent::Field($properties);
    }

    public function saveInto(DataObjectInterface $record)
    {
        if ($record->hasField($this->name) && $record->escapeTypeForField($this->name) != 'xml') {
            throw new Exception(
                'MarkdownEditorField->saveInto(): This field should save into a MarkdownText field.'
            );
        }

        $markdownValue = $this->Value();
        $this->extend('processHTML', $markdownValue);
        $record->{$this->name} = $markdownValue;
    }



}