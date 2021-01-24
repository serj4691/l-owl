<?php

namespace App\Http\Sections;

use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Display\DisplayInterface;
use SleepingOwl\Admin\Contracts\Form\FormInterface;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\SaveAndCreate;
use SleepingOwl\Admin\Section;
use SleepingOwl\Admin\Model\ModelConfigurationManager;

/**
 * Class Supply
 *
 * @property \App\Models\Supply $model
 *
 * @see https://sleepingowladmin.ru/#/ru/model_configuration_section
 */
class Supply extends Section implements Initializable
{
    /**
     * @var bool
     */
    protected $checkAccess = false;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $alias;

    /**
     * Initialize class.
     */
    public function initialize()
    {
        $this->addToNavigation()->setPriority(100)->setIcon('fa fa-lightbulb-o');
        $this->created(function($config, \Illuminate\Database\Eloquent\Model $model) {
            $supplies = \App\Models\Supply::all();
            foreach ($supplies as $sup){
                $item = $sup;
            }
            $count_last = $item['count_supply'];
            $sup = $item['number_supply'];
            $count = \App\Models\Order::all()->where('number_order','==', $sup);
            if ($count_last > $count[0]['count_order']) {
                $count_last = $count[0]['count_order'];
                $new_sup = \App\Models\Supply::find($item['id_supply']);
                $new_sup->count_supply = $count_last;
                $new_sup->save();
                $new_order = \App\Models\Order::find($count[0]['id_order']);
                $new_order->status = 'full';
                $new_order->count_order = 0;
                $new_order->save();

            }
            if ($count_last < $count[0]['count_order']) {
                $count_last = $count[0]['count_order'] - $count_last;
                $new_order = \App\Models\Order::find($count[0]['id_order']);
                $new_order->count_order = $count_last;
                $new_order->save();
            }

        });
    }

    /**
     * @param array $payload
     *
     * @return DisplayInterface
     */
    public function onDisplay($payload = [])
    {

        $columns = [
            AdminColumn::text('id_supply', '#')->setWidth('50px')->setHtmlAttribute('class', 'text-center'),
            AdminColumn::link('number_supply', 'Number')
                ->setSearchCallback(function($column, $query, $search){
                    return $query
                        ->orWhere('number_supply', 'like', '%'.$search.'%')
                        ->orWhere('created_at', 'like', '%'.$search.'%')
                    ;
                })
                ->setOrderable(function($query, $direction) {
                    $query->orderBy('created_at', $direction);
                })
            ,
            AdminColumn::text('delivery_name', 'Name of deliver'),
            AdminColumn::text('count_supply', 'Count of item'),
            //AdminColumn::boolean('number_supply', 'On'),
            AdminColumn::text('created_at', 'Created / updated', 'updated_at')
                ->setWidth('160px')
                ->setOrderable(function($query, $direction) {
                    $query->orderBy('updated_at', $direction);
                })
                ->setSearchable(false)
            ,
        ];

        $display = AdminDisplay::datatables()
            ->setName('firstdatatables')
            ->setOrder([[0, 'asc']])
            ->setDisplaySearch(true)
            ->paginate(25)
            ->setColumns($columns)
            ->setHtmlAttribute('class', 'table-primary table-hover th-center')
        ;

        $display->setColumnFilters([
            AdminColumnFilter::select()
                ->setModelForOptions(\App\Models\Supply::class, 'number_supply')
                ->setLoadOptionsQueryPreparer(function($element, $query) {
                    return $query;
                })
                ->setDisplay('number_supply')
                ->setColumnName('number')
                ->setPlaceholder('All numbers')
            ,
        ]);
        $display->getColumnFilters()->setPlacement('card.heading');

        return $display;
    }

    /**
     * @param int|null $id_supply
     * @param array $payload
     *
     * @return FormInterface
     */
    public function onEdit($id_supply = null, $payload = [])
    {
        $arrOrder[] = \App\Models\Order::all()->where('status','==', 'full')->toArray();
        $excl= [];
        foreach ($arrOrder[0] as $key => $item){
            $excl[] = $item['number_order'];
        }

        $form = AdminForm::card()->addBody([
            AdminFormElement::columns()->addColumn([
                AdminFormElement::select('number_supply', 'Number of order', \App\Models\Order::class)
                    ->setUsageKey('number_order')->setDisplay('number_order')->exclude($excl)->required(),
                AdminFormElement::select('delivery_name', 'Name of deliver', \App\Models\Delivery::class)
                    ->setUsageKey('title_delivery')->setDisplay('title_delivery')->required(),
                AdminFormElement::text('count_supply', 'Count of item')->required(),
                AdminFormElement::html('<hr>'),
                AdminFormElement::datetime('created_at')
                    ->setVisible(true)
                    ->setReadonly(false),
                AdminFormElement::html('last AdminFormElement without comma')
            ], 'col-xs-12 col-sm-6 col-md-4 col-lg-4')->addColumn([
                AdminFormElement::text('id_supply', 'ID')->setReadonly(true),
                AdminFormElement::html('last AdminFormElement without comma')
            ], 'col-xs-12 col-sm-6 col-md-8 col-lg-8'),

        ]);

        $form->getButtons()->setButtons([
            'save'  => new Save(),
            'save_and_close'  => new SaveAndClose(),
            'save_and_create'  => new SaveAndCreate(),
            'cancel'  => (new Cancel()),
        ]);

        return $form;


    }

    /**
     * @return FormInterface
     */
    public function onCreate($payload = [])
    {
        return $this->onEdit(null, $payload);
    }

    /**
     * @return bool
     */
    public function isDeletable(Model $model)
    {
        return true;
    }

    /**
     * @return void
     */
    public function onRestore($id)
    {
        // remove if unused
    }
}
