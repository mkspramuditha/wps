<?php


namespace AppBundle\Controller;
use AppBundle\Entity\Brand;
use AppBundle\Entity\Item;
use AppBundle\Entity\Product;
use AppBundle\Entity\Vehicle;
use Symfony\Component\Config\Definition\Exception\DuplicateKeyException;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends DefaultController
{
    /**
     * @Route("/loadFeatures",name="load_Features")
     */
    public function loadFeaturesAction(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Product');
        $productMap = array();

        foreach ($repository->findAll() as $product){
            $productMap[$product->getProductId()] = $product;
        }

        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/products?include=features&page[size]=10000');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/products?include=features&page[size]=10000&page[cursor]='.$next);
            }
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
//            var_dump(count($products));exit;

            foreach ($products as $product){
                if(!key_exists($product['id'],$productMap)){
                    continue;
                }
                $productObj = $productMap[$product['id']];
                $features = $product['features']['data'];
                $featuresHtml = "<ul>";
                foreach ($features as $feature){
                    $featuresHtml.="<li>".$feature['name']."</li>";
                }
                $featuresHtml.="</ul>";

                if(count($features) == 0){
                    $productObj->setFeatures("");
                }else{
                    $productObj->setFeatures($featuresHtml);
                }

                $em->persist($productObj);
            }
            $em->flush();

            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
            }
        }

        exit;


    }

    /**
     * @Route("/loadVehicles",name="load_vehicles")
     */
    public function loadVehicleAction(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Vehicle');
        $vehicles = $repository->findAll();
        foreach ($vehicles as $vehicle){
            $em->remove($vehicle);
        }

        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/vehicles?include=vehiclemodel.vehiclemake,vehicleyear&page[size]=10000');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/vehicles?include=vehiclemodel.vehiclemake,vehicleyear&page[size]=10000&page[cursor]='.$next);
            }
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
            $vehicles = $result['data'];
//            var_dump(count($vehicles));exit;

            foreach ($vehicles as $vehicle){
//                var_dump($vehicle['id']);
//                var_dump($vehicle['vehiclemodel']['data']['vehiclemake']['name']);exit;
                $vehicleObj = new Vehicle();
                $vehicleObj->setVehicleId($vehicle['id']);
                $vehicleObj->setMake($vehicle['vehiclemodel']['data']['vehiclemake']['data']['name']);
                $vehicleObj->setModel($vehicle['vehiclemodel']['data']['name']);
                $vehicleObj->setYear($vehicle['vehicleyear']['data']['name']);
                $em->persist($vehicleObj);
            }
            $em->flush();

            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
            }
        }

        exit;


    }

    /**
 * @Route("/loadProducts",name="load_products")
 */
    public function loadProducts(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Product');
        $products = $repository->findAll();
        foreach ($products as $product){
            $em->remove($product);
        }

        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/products?page[size]=10000');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/products?page[size]=10000&page[cursor]='.$next);
            }
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

            foreach ($products as $product){
                $productObj = new Product();
                $productObj->setProductId($product['id']);
                $productObj->setName($product['name']);
                $productObj->setDescription($product['description']);
                $productObj->setCreatedAt($product['created_at']);
                $productObj->setUpdatedAt($product['updated_at']);


                $em->persist($productObj);
            }
            $em->flush();

            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
            }
        }

        exit;


    }

    /**
     * @Route("/loadItems",name="load_items")
     */
    public function loadItems(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Item');
//        $items = $repository->findAll();
//        foreach ($items as $item){
//            $em->remove($item);
//        }

//        $vehicles = $this->getRepository('Vehicle')->findAll();
//        $vehicleMap = array();
//        foreach ($vehicles as $vehicle){
//            $vehicleMap[$vehicle->getVehicleId()] = $vehicle;
//        }

        $next = '1poYD2mRAMaD';
//        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=quantities');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=quantities&page[cursor]='.$next);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);


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
//            var_dump($result);exit;
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            $result = json_decode($result,true);
            $items = $result['data'];

            foreach ($items as $item){

                if($item['product_id'] == null){
                    continue;
                }

                $itemObj = new Item();
                $itemObj->setItemId($item['id']);
                $itemObj->setProductId($item['product_id']);
                $itemObj->setBrandId($item['brand_id']);
                $itemObj->setCountryId($item['country_id']);
                $itemObj->setSku($item['sku']);
                $itemObj->setName($item['name']);
                $itemObj->setListPrice($item['list_price']);
                $itemObj->setSupplierProductId($item['supplier_product_id']);
                $itemObj->setDealerPrice($item['standard_dealer_price']);
                $itemObj->setUpc($item['upc']);
                $itemObj->setCreatedAt($item['created_at']);
                $itemObj->setUpdatedAt($item['updated_at']);

//                $images = $item['images']['data'];
//                $imageCount = count($images);
//                if($imageCount > 0){
//                    $itemObj->setImage1($this->getImageUrl($images[0]));
//                }
//                if($imageCount > 1){
//                    $itemObj->setImage2($this->getImageUrl($images[1]));
//                }
//                if($imageCount > 2){
//                    $itemObj->setImage3($this->getImageUrl($images[2]));
//                }

                $quantity = 0;

                $quantities = $item['quantities']['data'];
                foreach ($quantities as $qty){
                    $quantity+= $qty['obtainable'];
                }

                $itemObj->setQuantity($quantity);
//                $vehicles = $item['vehicles']['data'];
//                $vehicleIds = array();
//                foreach ($vehicles as $vehicle){
//                    $vehicleIds[] = $vehicle['id'];
//                }
//
//                $itemObj->setVehicles(join(',',$vehicleIds));

                try{
                    if (!$em->isOpen()) {
                        $em = $em->create(
                            $em->getConnection(),
                            $em->getConfiguration()
                        );
                    }
                    $em->persist($itemObj);
                }catch (\Exception $e){
                    var_dump($item['id']);
                    var_dump($e->getMessage());
//                    exit;
                }

            }

            $em->flush();

//            exit;
            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
                var_dump($next);
//                exit;
            }
        }

        exit;


    }

    /**
     * @Route("/loadBrands",name="load_brands")
     */
    public function loadBrands(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Brand');
        $brands = $repository->findAll();
        foreach ($brands as $brand){
            $em->remove($brand);
        }

        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/brands?page[size]=10000');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/brands?page[size]=10000&page[cursor]='.$next);
            }
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
            $brands = $result['data'];

            foreach ($brands as $brand){
                $brandObj = new Brand();
                $brandObj->setName($brand['name']);
                $brandObj->setBrandId($brand['id']);


                $em->persist($brandObj);
            }
            $em->flush();

            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
            }
        }

        exit;


    }

    /**
     * @Route("/loadImages",name="load_images")
     */
    public function loadImages(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Item');
//        $items = $repository->findAll();
//        foreach ($items as $item){
//            $em->remove($item);
//        }

//        $vehicles = $this->getRepository('Vehicle')->findAll();
//        $vehicleMap = array();
//        foreach ($vehicles as $vehicle){
//            $vehicleMap[$vehicle->getVehicleId()] = $vehicle;
//        }

        $next = '1poYD2bopMaD';
//        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=images');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=images&page[cursor]='.$next);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);


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
//            var_dump($result);exit;
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);

            $result = json_decode($result,true);
            $items = $result['data'];

            foreach ($items as $item){

                if($item['product_id'] == null){
                    continue;
                }

                $itemObj = $repository->findOneBy(array('itemId'=>$item['id']));
                if($itemObj == null){
                    continue;
                }
//                $itemObj->setItemId($item['id']);
//                $itemObj->setProductId($item['product_id']);
//                $itemObj->setBrandId($item['brand_id']);
//                $itemObj->setCountryId($item['country_id']);
//                $itemObj->setSku($item['sku']);
//                $itemObj->setName($item['name']);
//                $itemObj->setListPrice($item['list_price']);
//                $itemObj->setSupplierProductId($item['supplier_product_id']);
//                $itemObj->setDealerPrice($item['standard_dealer_price']);
//                $itemObj->setUpc($item['upc']);
                $itemObj->setCreatedAt($item['created_at']);
                $itemObj->setUpdatedAt($item['updated_at']);

                $images = $item['images']['data'];
                $imageCount = count($images);
                if($imageCount > 0){
                    $itemObj->setImage1($this->getImageUrl($images[0]));
                }
                if($imageCount > 1){
                    $itemObj->setImage2($this->getImageUrl($images[1]));
                }
                if($imageCount > 2){
                    $itemObj->setImage3($this->getImageUrl($images[2]));
                }

//                $quantity = 0;
//
//                $quantities = $item['quantities']['data'];
//                foreach ($quantities as $qty){
//                    $quantity+= $qty['obtainable'];
//                }
//
//                $itemObj->setQuantity($quantity);


//                $vehicles = $item['vehicles']['data'];
//                $vehicleIds = array();
//                foreach ($vehicles as $vehicle){
//                    $vehicleIds[] = $vehicle['id'];
//                }
//
//                $itemObj->setVehicles(join(',',$vehicleIds));

                try{
                    if (!$em->isOpen()) {
                        $em = $em->create(
                            $em->getConnection(),
                            $em->getConfiguration()
                        );
                    }
                    $em->persist($itemObj);
                }catch (\Exception $e){
                    var_dump($item['id']);
                    var_dump($e->getMessage());
//                    exit;
                }

            }

            $em->flush();

//            exit;
            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
                var_dump($next);
//                exit;
            }
        }

        exit;


    }


    /**
     * @Route("/loadVehicles",name="load_vehicles")
     */
    public function loadVehicles(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Item');
//        $items = $repository->findAll();
//        foreach ($items as $item){
//            $em->remove($item);
//        }

//        $vehicles = $this->getRepository('Vehicle')->findAll();
//        $vehicleMap = array();
//        foreach ($vehicles as $vehicle){
//            $vehicleMap[$vehicle->getVehicleId()] = $vehicle;
//        }

        $next = 'bWBe8X8bwe23';
//        $next = null;

        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=500&include=vehicles');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=5&include=vehicles&page[cursor]='.$next);
//                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=500&include=vehicles&page[cursor]='.$next);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);


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
//            var_dump($result);exit;
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
//            var_dump($result);exit;

            $result = json_decode($result,true);
            $items = $result['data'];

            foreach ($items as $item){

                if($item['product_id'] == null){
                    continue;
                }

                $itemObj = $repository->findOneBy(array('itemId'=>$item['id']));
                if($itemObj == null){
                    continue;
                }

                $itemObj->setLength($item['length']);
                $itemObj->setWidth($item['width']);
                $itemObj->setHeight($item['height']);
                $itemObj->setWeight($item['weight']);

                $vehicles = $item['vehicles']['data'];
                $vehicleIds = array();
                foreach ($vehicles as $vehicle){
                    $vehicleIds[] = $vehicle['id'];
                }

                $itemObj->setVehicles(join(',',$vehicleIds));

                try{
                    if (!$em->isOpen()) {
                        $em = $em->create(
                            $em->getConnection(),
                            $em->getConfiguration()
                        );
                    }
                    $em->persist($itemObj);
                }catch (\Exception $e){
                    var_dump($item['id']);
                    var_dump($e->getMessage());
//                    exit;
                }

            }

            $em->flush();

//            exit;
            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
                var_dump($next);
//                exit;
            }
        }

        exit;


    }


    /**
     * @Route("/loadType",name="load_type")
     */
    public function loadType(){

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getRepository('Item');
//        $items = $repository->findAll();
//        foreach ($items as $item){
//            $em->remove($item);
//        }

//        $vehicles = $this->getRepository('Vehicle')->findAll();
//        $vehicleMap = array();
//        foreach ($vehicles as $vehicle){
//            $vehicleMap[$vehicle->getVehicleId()] = $vehicle;
//        }

        $next = '1poYD2wokMaD';
//        $next = null;

//        $em->flush();

        while (true){
            $ch = curl_init();

            if($next == null){
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=attributevalues');
            }else{
                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=10000&include=attributevalues&page[cursor]='.$next);
//                curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items?page[size]=500&include=vehicles&page[cursor]='.$next);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
            curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);


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
//            var_dump($result);exit;
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
//            var_dump($result);exit;

            $result = json_decode($result,true);
            $items = $result['data'];

            foreach ($items as $item){

                if($item['product_id'] == null){
                    continue;
                }

                $itemObj = $repository->findOneBy(array('itemId'=>$item['id']));
                if($itemObj == null){
                    continue;
                }

                $attributes = $item['attributevalues']['data'];
                $type = null;
                foreach ($attributes as $attribute){
                    if($attribute['attributekey_id'] == 1){
                        $type = $attribute['name'];
                        break;
                    }
                }

                $itemObj->setType($type);


                try{
                    if (!$em->isOpen()) {
                        $em = $em->create(
                            $em->getConnection(),
                            $em->getConfiguration()
                        );
                    }
                    $em->persist($itemObj);
                }catch (\Exception $e){
                    var_dump($item['id']);
                    var_dump($e->getMessage());
//                    exit;
                }

            }

            $em->flush();

//            exit;
            if($result['meta']['cursor']['next'] == null){
                break;
            }else{
                $next = $result['meta']['cursor']['next'];
                var_dump($next);
//                exit;
            }
        }

        exit;


    }



    private function getImageUrl($image){
        return 'https://'.$image['domain'].$image['path'].'full/'.$image['filename'];
    }

    public function updateItem($itemId){
        $itemObj = $this->getRepository('Item')->findOneBy(array('itemId'=>$itemId));

        if($itemObj == null){
            $itemObj = new Item();
        }

        $ch = curl_init();


        curl_setopt($ch, CURLOPT_URL, 'http://api.wps-inc.com/items/'.$itemId.'?include=quantities,images,vehicles');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TCP_KEEPALIVE, 1);
        curl_setopt($ch, CURLOPT_TCP_KEEPIDLE, 2);


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
        $item = $result['data'];


//        $itemObj->setItemId($item['id']);
        $itemObj->setProductId($item['product_id']);
        $itemObj->setBrandId($item['brand_id']);
        $itemObj->setCountryId($item['country_id']);
        $itemObj->setSku($item['sku']);
        $itemObj->setName($item['name']);
        $itemObj->setListPrice($item['list_price']);
        $itemObj->setSupplierProductId($item['supplier_product_id']);
        $itemObj->setDealerPrice($item['standard_dealer_price']);
        $itemObj->setUpc($item['upc']);
        $itemObj->setCreatedAt($item['created_at']);
        $itemObj->setUpdatedAt($item['updated_at']);

        $itemObj->setLength($item['length']);
        $itemObj->setWidth($item['width']);
        $itemObj->setHeight($item['height']);
        $itemObj->setWeight($item['weight']);

        $images = $item['images']['data'];
        $imageCount = count($images);
        if($imageCount > 0){
            $itemObj->setImage1($this->getImageUrl($images[0]));
        }
        if($imageCount > 1){
            $itemObj->setImage2($this->getImageUrl($images[1]));
        }
        if($imageCount > 2){
            $itemObj->setImage3($this->getImageUrl($images[2]));
        }

        $quantity = 0;

        $quantities = $item['quantities']['data'];
        foreach ($quantities as $qty){
            $quantity+= $qty['obtainable'];
        }

        $itemObj->setQuantity($quantity);
        $vehicles = $item['vehicles']['data'];
        $vehicleIds = array();
        foreach ($vehicles as $vehicle){
            $vehicleIds[] = $vehicle['id'];
        }

        $itemObj->setVehicles(join(',',$vehicleIds));

        $this->insert($itemObj);

    }

    /**
     * @Route("/test",name="test")
     */
    public function testAction(){

        $items = $this->getRepository('Item')->findAll();

        foreach ($items as $item){
            $vehicles = $item->getVehicles();
//            exit;
            if($vehicles != null && strlen($vehicles) > 200){

                var_dump($item->getItemId());

                $this->updateItem($item->getItemId());
            }
        }

        exit;
    }


}