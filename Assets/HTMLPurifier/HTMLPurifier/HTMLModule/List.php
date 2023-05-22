<?php

/**
 * XHTML 1.1 List Module, defines list-oriented elements. Core Module.
 */
class HTMLPurifier_HTMLModule_List extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'List';

    // According to the abstract schema, the list content set is a fully formed
    // one or more expr, but it invariably occurs in an optional declaration
    // so we're not going to do that subtlety. it might cause trouble
    // if a user defines "list" and expects that multiple lists are
    // allowed to be specified, but then again, that's not very intuitive.
    // Furthermore, the actual xml schema may disagree. regardless,
    // we don't have support for such nested expressions without using
    // the incredibly inefficient and draconic custom childdef.

    /**
     * @type array
     */
    public $content_sets = array('Flow' => 'List');

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $ol = $this->addElement('ol', 'List', new HTMLPurifier_ChildDef_List(), 'Common');
        $ul = $this->addElement('ul', 'List', new HTMLPurifier_ChildDef_List(), 'Common');
        // Xxx the wrap attribute is handled by makewellformed.  this is all
        // quite unsatisfactory, because we generated this
        // *specifically* for lists, and now a big chunk of the handling
        // is done properly by the list childdef.  so actually, we just
        // want enough information to make autoclosing work properly,
        // and then hand off the tricky stuff to the childdef.
        $ol->wrap = 'li';
        $ul->wrap = 'li';
        $this->addElement('dl', 'List', 'Required: dt | dd', 'Common');

        $this->addElement('li', false, 'Flow', 'Common');

        $this->addElement('dd', false, 'Flow', 'Common');
        $this->addElement('dt', false, 'Inline', 'Common');
    }
}

// vim: et sw=4 sts=4
