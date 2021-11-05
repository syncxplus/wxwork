<?php

namespace app;

use app\common\AppBase;
use data\SqlMapper;

class Product extends AppBase
{
    function get($f3)
    {
        $errMsg = '';
        $products = [];
        $keyword = $f3->get('GET.keyword') ?? false;
        if ($keyword) {
            $f3->set('keyword', $keyword);
            $query = ['model like ?', $keyword . '%'];
            $product = new SqlMapper('prototype');
            $count = $product->count($query);
            if ($count == 0) {
               $errMsg = '找不到对应的产品';
            } else if ($count > 10) {
                $errMsg = '模糊匹配过多产品，请输入更精确的SKU';
            } else {
                $product->load($query);
                while (!$product->dry()) {
                    $data = $product->cast();
                    $price = json_decode($data['price'], true);
                    if (json_last_error()) {
                        $data['price'] = [
                            $data['manufactory'] => ['cost' => $data['cost']],
                        ];
                    } else {
                        $data['price'] = $price;
                    }
                    $products[] = $data;
                    $product->next();
                }
            }
        }
        $f3->set('title', '查询产品');
        $f3->set('errMsg', $errMsg);
        $f3->set('products', $products);
        echo \Template::instance()->render('product.html');
    }
}
