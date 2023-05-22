<?php

/**
 * XHTML 1.1 Edit Module, defines editing-related elements. Text Extension
 * Module.
 */
class HTMLPurifier_HTMLModule_Edit extends HTMLPurifier_HTMLModule
{

    /**
     * @type string
     */
    public $name = 'Edit';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $contents = 'Chameleon: #PCDATA | Inline ! #PCDATA | Flow';
        $attr = array(
            'cite' => 'URI',
            // 'datetime' => 'datetime', // not implemented
        );
        $this->addElement('del', 'Inline', $contents, 'Common', $attr);
        $this->addElement('ins', 'Inline', $contents, 'Common', $attr);
    }

    // Html 4.01 specifies that ins/del must not contain block
    // elements when used in an inline context, chameleon is
    // a complicated workaround to acheive this effect

    // Inline context ! block context (exclamation mark is
    // separator, see getchilddef for parsing)

    /**
     * @type bool
     */
    public $defines_child_def = true;

    /**
     * @param HTMLPurifier_ElementDef $def
     * @return HTMLPurifier_ChildDef_Chameleon
     */
    public function getChildDef($def)
    {
        if ($def->content_model_type != 'chameleon') {
            return false;
        }
        $value = explode('!', $def->content_model);
        return new HTMLPurifier_ChildDef_Chameleon($value[0], $value[1]);
    }
}

// vim: et sw=4 sts=4
