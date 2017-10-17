<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/18/15
 * Time: 11:11 AM
 * To change this template use File | Settings | File Templates.
 */
namespace SilverStripers\markdown\db;



use SilverStripe\Forms\TextField;
use SilverStripe\View\Parsers\ShortcodeParser;
use cebe\markdown\GithubMarkdown;
use SilverStripers\markdown\MarkdownEditorField;

class MarkdownText extends \SilverStripe\ORM\FieldType\DBText
{

    private static $escape_type = 'xml';

    private static $casting = array(
        "AbsoluteLinks"         => "HTMLText",
        "BigSummary"            => "HTMLText",
        "ContextSummary" => "HTMLText",
        "FirstParagraph" => "HTMLText",
        "FirstSentence" => "HTMLText",
        "LimitCharacters" => "HTMLText",
        "LimitSentences" => "HTMLText",
        "Lower" => "HTMLText",
        "LowerCase" => "HTMLText",
        "Summary" => "HTMLText",
        "Upper" => "HTMLText",
        "UpperCase" => "HTMLText",
        'EscapeXML' => 'HTMLText',
        'LimitWordCount' => 'HTMLText',
        'LimitWordCountXML' => 'HTMLText',
        'NoHTML' => 'Text',
    );

    private static $markdown_as_base = false;
    
    private $parsedContent;
    private $shortcodes = array();


    /**
     * @return string
     * parse contents of the markdown field to tempates
     */
    public function ParseMarkdown($bCache = true, $strValue = '')
    {
        if ($bCache && $this->parsedContent) {
            return $this->parsedContent;
        }

        $parsed = !empty($strValue) ? $strValue : $this->value;

        $this->shortcodes = array();

        // shortcodes
        $regexes = array(
            '/\[image_link*\s[a-z|A-Z|0-9\s\=]*\]/',
            '/\[file_link\,[a-z|A-Z|0-9\s\=]*\]/'
        );
        
        foreach ($regexes as $pattern) {
            preg_match_all($pattern, $parsed, $matches);
            if(!empty($matches[0])) foreach ($matches[0] as $attachment) {
                $this->shortcodes[md5($attachment)] = $attachment;
                $parsed = str_replace($attachment, md5($attachment), $parsed);
            }
        }

        $parseDown = new GithubMarkdown();
        $parsed  = $parseDown->parse($parsed);

        foreach ($this->shortcodes as $key => $shortcode) {
            $parsed = str_replace($key, $shortcode, $parsed);
        }


        $shortCodeParser = ShortcodeParser::get_active();
        $parsed = $shortCodeParser->parse($parsed);

        $this->parsedContent = $parsed;
        return $parsed;
    }



    /**
     * @return string
     */
    public function forTemplate()
    {
        return $this->ParseMarkdown();
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * @param null $title
     * @param null $params
     * @return FormField|MarkdownEditorField|NullableField|TextareaField
     */
    public function scaffoldFormField($title = null, $params = null)
    {
        return new MarkdownEditorField($this->name, $title);
    }


    /**
     * @param null $title
     * @param null $params
     * @return FormField|TextField
     */
    public function scaffoldSearchField($title = null, $params = null)
    {
        return new TextField($this->name, $title);
    }


    /**
     * @return string
     */
    public function NoHTML()
    {
        return strip_tags($this->ParseMarkdown());
    }

    /**
     * @return string
     */
    public function Upper()
    {
        $strValue = strtoupper($this->__toString());
        return $this->ParseMarkdown(false, $strValue);
    }

    /**
     * @return string
     */
    public function UpperCase()
    {
        return $this->Upper();
    }


    /**
     * @return string
     */
    public function Lower()
    {
        $strValue = strtolower($this->__toString());
        return $this->ParseMarkdown(false, $strValue);
    }

    /**
     * @return string
     */
    public function LowerCase()
    {
        return $this->Lower();
    }
}