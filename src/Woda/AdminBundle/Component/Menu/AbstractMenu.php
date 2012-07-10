<?php

namespace Woda\AdminBundle\Component\Menu;

use Woda\AdminBundle\Component\Menu\Link;
use RecursiveIterator;

abstract class AbstractMenu implements RecursiveIterator
{
    private $array = array();
    private $id = 0;

    public $options = array(
        'translation_domain' => 'messages',
        'label' => null,
    );

    public function addContainer(AbstractMenu $navigation, $weight = 0)
    {
        $this->add($navigation, $weight);
    }

    public function addLink($label, $target, array $options = array())
    {
        $options = array_merge(array(
            'translation_domain' => $this->options['translation_domain'],
            'icon' => 'icon-chevron-right',
            'weight' => 0,
        ), $options);

        $this->add(new Link($label, $target, array(
            'translation_domain' => $options['translation_domain'],
            'icon' => $options['icon'],
        )), $options['weight']);
    }

    private function add($object, $weight)
    {
        if (!isset($this->array[$weight])) {
            $this->array[$weight] = array();
        }

        $this->array[$weight][] = $object;
    }

    public function hasChildren()
    {
        return current(current($this->array)) instanceof AbstractMenu;
    }

    public function getChildren()
    {
        return current(current($this->array));
    }

    public function rewind()
    {
        $this->id = 0;

        ksort($this->array);

        if (($key = key($this->array)) !== null) {
            reset($this->array[$key]);
        }
    }

    public function current()
    {
        return current(current($this->array));
    }

    public function key()
    {
        return $this->id;
    }

    public function next()
    {
        ++$this->id;

        $key = key($this->array);

        $sub =& $this->array[$key];
        next($sub);

        if (key($sub) === null) {
            reset($sub);
            next($this->array);
        }
    }

    public function valid()
    {
        if (($key = key($this->array)) === null)
            return false;

        if (key($this->array[$key]) === null)
            return false;

        return true;
    }
}
