<?php
Class Inventory{

    public static function decreaseByItemNumber($item_number,$qty){

        if($item_number == ''){
            return false;
        }

        $item = ORM::for_table('sys_items')->where('item_number',$item_number)->first();

        if(!$item && is_numeric($item_number)){
            $item = Item::find($item_number);
        }

        if($item){

            $current_qty = $item->inventory;

            $updated_qty = $current_qty-$qty;


            $item->inventory = $updated_qty;

            $item->save();

            return true;

        }

        return false;

    }


    public static function increaseByItemNumber($item_number,$qty){

        if($item_number == ''){
            return false;
        }

        $item = ORM::for_table('sys_items')->where('item_number',$item_number)->first();

        if($item){

            $current_qty = $item->inventory;

            $updated_qty = $current_qty+$qty;

            $item->inventory = $updated_qty;

            $item->save();

            return true;

        }

        return false;

    }

    public static function recountItemByItemNumber($item_number)
    {
        $invoice_items = InvoiceItem::where('item_number',$item_number)->get();
    }

}
