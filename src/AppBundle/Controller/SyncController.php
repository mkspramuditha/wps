<?php


namespace AppBundle\Controller;
use Symfony\Component\Routing\Annotation\Route;


class SyncController extends DefaultController
{
    /**
     * @Route("/productSync", name="productSync")
     */
    public function productSync(){
        $items = $this->getRepository('Item')->findBy(array('shopifyProductId'=>null),array(),1000);

        foreach ($items as $item){

            $product = $this->getRepository('Product')->findOneBy(array('productId'=>$item->getProductId()));

            $productObj = array();

            $productObj['published'] = false;


            $variantObj = array();
            $images = array();

            $productObj['product_type'] = $item->getType();

            $variantObj['price'] = $item->getListprice();
            $variantObj['sku'] = $item->getSku();
            $variantObj['weight'] = $item->getWeight();
            $variantObj['weight_unit'] = 'lb';
            $variantObj['barcode'] = $item->getUpc();
            $variantObj['inventory_quantity'] = $item->getQuantity();
            $variantObj['option1'] =  $item->getSku();
            $vehicleIds = explode(",",$item->getVehicles());
            if($item->getImage1() != null || $item->getImage1() != ""){
                $images[] = array('src'=>$item->getImage1());

            }

            if($item->getImage2() != null || $item->getImage2() != ""){
                $images[] =  array('src'=>$item->getImage2());

            }

            if($item->getImage3() != null || $item->getImage3() != ""){
                $images[] =  array('src'=>$item->getImage3());

            }

            $brand = $this->getRepository('Brand')->findOneBy(array('brandId'=>$item->getBrandId()));

            $variantObj['title'] = $item->getName(). " ".$item->getSupplierProductId();
            $productObj['title'] = $product->getName(). " ".$item->getSupplierProductId();

            if($brand != null){
                $productObj['vendor'] = $brand->getName();
            }else{
                $productObj['vendor'] = "";
            }

            $vehicles = $this->getRepository('Vehicle')->getVehicles($vehicleIds);
            $bodyHtml = $this->getVehicleTable($vehicles,$product->getDescription());
            if($product->getFeatures() != "" && $product->getFeatures() != null){
                $bodyHtml = "<h2>Features</h2>".$product->getFeatures()."<hr>".$bodyHtml;
            }
            $productObj['body_html'] = $bodyHtml;
            $productObj['variants'] = [$variantObj];
            $productObj['images'] = $images;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://77faf5cc6d0bfa48364218794705bc0b:73e01bb52b7f38cc0294f04e8fdd61f2@allterraindepot.myshopify.com/admin/api/2019-10/products.json",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>json_encode(array('product'=>$productObj)),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
//                "Authorization: Basic NzdmYWY1Y2M2ZDBiZmE0ODM2NDIxODc5NDcwNWJjMGI6NzNlMDFiYjUyYjdmMzhjYzAyOTRmMDRlOGZkZDYxZjI="
                ),
            ));

            $response = curl_exec($curl);
//                var_dump($response);exit;
            curl_close($curl);
            $response = json_decode($response,true);
            try{
                $product_id = $response['product']['id'];
                $item->setShopifyProductId($product_id);
                $collection_id =158042325097;
                ($this->addToCollection($product_id,$collection_id));

                $this->insert($item);

            }catch (\Exception $e){
                var_dump($e->getMessage());
            }
        }
    }


    private function getVehicleTable($vehicles,$description){
        if($description == null){
            $description = "";
        }
        $html = '<table><thead><th>Make</th><th>Model</th><th>Year</th></thead><tbody>';
        foreach ($vehicles as $vehicle){
            $html.='<tr><td>'.$vehicle->getMake().'</td><td>'.$vehicle->getModel().'</td><td>'.$vehicle->getYear().'</td></tr>';
        }

        $html.='</tbody></table>';

        $html = $description . '<hr>'.$html;
        return $html;
    }

    private function addToCollection($productId,$collectionId){
        $body = array(
            'collect'=>array(
                'product_id'=>$productId,
                'collection_id'=>$collectionId
            )
        );

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://77faf5cc6d0bfa48364218794705bc0b:73e01bb52b7f38cc0294f04e8fdd61f2@allterraindepot.myshopify.com/admin/api/2019-10/collects.json",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>json_encode($body),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Basic NzdmYWY1Y2M2ZDBiZmE0ODM2NDIxODc5NDcwNWJjMGI6NzNlMDFiYjUyYjdmMzhjYzAyOTRmMDRlOGZkZDYxZjI="
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);


        return $response;
    }

}