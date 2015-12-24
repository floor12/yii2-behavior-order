<?php

namespace floor12\orderBehavior;

use yii\base\Behavior;

/**
 * Description of OrderBehavior
 *
 * @author floor12
 */

class OrderBehavior extends Behavior {

    public function order($mode = 0) {
        if ($this->order < 2)
            return true;
        $oldOrder = $this->order;
        if (!$mode) {
            $this->order--;
        } else {
            $this->order++;
        }
        $obj = Page::findByCondition(['order' => $this->order, 'parent_id' => (int) $this->parent_id])->one();

        if ($obj) {
            $obj->order = $oldOrder;
            $obj->save();
        }
        $this->save();
        $this->reorder();
    }

    public function reorder() {
        $rows = Page::find()->where('parent_id=:id', ['id' => $this->parent_id])->orderBy('order')->all();
        if ($rows)
            foreach ($rows as $key => $row) {
                $row->order = ++$key;
                $row->save();
            }
    }

}
