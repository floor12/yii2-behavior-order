<?php

namespace floor12\orderBehavior;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii;

/**
 * Description of OrderBehavior
 *
 * @author floor12
 */
class OrderBehavior extends Behavior {

    public $params = [];

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
        ];
    }

    public function beforeInsert() {
        $condition = "1=1";
        if ($this->params)
            foreach ($this->params as $field) {
                $condition.= " AND `{$field}`='{$this->owner->$field}'";
            }

        $num = \Yii::$app->db->createCommand("SELECT count('id') as num FROM {$this->owner->tableName()} WHERE {$condition}")->queryOne();
        $this->owner->order = ++$num['num'];
    }

    public function order($mode = 0) {
        if ($this->owner->order < 2)
            return true;
        $oldOrder = $this->owner->order;
        if (!$mode) {
            $this->owner->order--;
        } else {
            $this->owner->order++;
        }

        $condition = "`order`={$this->owner->order}";
        if ($this->params)
            foreach ($this->params as $field) {
                $condition.= " AND `{$field}`='{$this->owner->$field}'";
            }
        $command = yii::$app->db->createCommand()->update($this->owner->tableName(), ['order' => $oldOrder], $condition)->execute();

        $this->owner->save();
        $this->reorder();
    }

    public function reorder() {
        $class = get_class($this->owner);
        $condition = "1=1";
        if ($this->params)
            foreach ($this->params as $field) {
                $condition.= " AND `{$field}`='{$this->owner->$field}'";
            }
        $rows = $class::find()->where($condition)->orderBy('order')->all();
        if ($rows)
            foreach ($rows as $key => $row) {
                $row->order = ++$key;
                $row->save(false);
            }
    }

}
