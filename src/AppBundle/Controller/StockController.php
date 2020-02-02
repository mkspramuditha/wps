<?php


namespace AppBundle\Controller;
use AppBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends DefaultController
{

    /**
     * @Route("/updateStocks", name="update_stocks")
     */
    public function updateStockAction(Request $request){
        $productsToUpdate = $this->getRepository('Item')->getProductsToUpdateStock(100);
        $productsCount = count($productsToUpdate);
        $itemMap = array();
        $itemIdList = array();

        foreach ($productsToUpdate as $item){
            $itemIdList[] = $item->getItemId();
            $itemMap[$item->getItemId()] = $item;
        }
        $itemIdString = implode(",",$itemIdList);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items/'.$itemIdString.'?page[size]=100&include=quantities');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Encoding: gzip, deflate';
        $headers[] = 'Authorization: Bearer 1SdvWxpjlyzJccMon68GMnwDVPG3o7QgluyfLuTZ';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Connection: keep-alive';
        $headers[] = 'Host: api.wps-inc.com';
        $headers[] = 'Postman-Token: 87a9bff2-fcca-4721-a6f3-f7c068fac300,5c42a481-3653-40b5-b86e-92ef2f116c7a';
        $headers[] = 'User-Agent: PostmanRuntime/7.20.1';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result,true);
        $products = $result['data'];

        if($productsCount == 1){
            $products = [$products];
        }

        foreach ($products as $product){
//            var_dump($product['id']);
            $item = $itemMap[$product['id']];
            if($item == null){
                var_dump("item not found");
                continue;
            }
            $newStockCount = 0;
            foreach ($product['quantities']['data'] as $stock){
                $newStockCount+= $stock['obtainable'];
            }
//            var_dump($newStockCount);
            if($newStockCount != $item->getQuantity()){
                $item->setQuantity($newStockCount);
                $item->setUpdatedAt($product['updated_at']);
                $status = $this->updateProductInShopify($item);
                if($status){
                    var_dump($product['id']);
                    $item->setStockUpdatedDate(new \DateTime());
                }
            }else{
                $item->setStockUpdatedDate(new \DateTime());
            }
            $this->insert($item);
        }


        var_dump(count($productsToUpdate));
        exit;
    }


    private function updateProductInShopify($product){

        try{
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://77faf5cc6d0bfa48364218794705bc0b:73e01bb52b7f38cc0294f04e8fdd61f2@allterraindepot.myshopify.com/admin/inventory_levels/set.json",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>"{\r\n  \"location_id\": 27948044,\r\n  \"inventory_item_id\": ".$product->getShopifyVariantId().",\r\n  \"available\": ".$product->getQuantity()."\r\n}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Authorization: Basic NzdmYWY1Y2M2ZDBiZmE0ODM2NDIxODc5NDcwNWJjMGI6NzNlMDFiYjUyYjdmMzhjYzAyOTRmMDRlOGZkZDYxZjI="
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response, true);
//            var_dump($response);
            if(key_exists('inventory_level',$response)){
                return true;
            }
        }catch (\Exception $e){
            var_dump($e->getMessage());
        }

        return false;

    }


    private function convertProductToJson($item,$stock){
//        var_dump($stock);exit;
        $productObj = array();
        $productObj['id'] = $item->getShopifyProductId();
        $variantObj = array();
        $variantObj['title'] = $item->getSku();
        $variantObj['price'] = $item->getListPrice();
        $variantObj['sku'] = $item->getSku();
        $variantObj['inventory_management'] = 'shopify';
        $variantObj['option1'] = $item->getSku();
        $variantObj['barcode'] = $item->getUpc();
        $variantObj['weight'] = $item->getWeight();
        $variantObj['weight_unit'] = 'lb';
        $variantObj['inventory_quantity'] = $stock;
        $productObj['variants'] = [$variantObj];
        var_dump($productObj);exit;
        return $productObj;



    }


}