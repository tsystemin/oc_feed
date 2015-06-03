<?php

class ControllerFeedRestApi extends Controller {

	private $debugIt = false;

	public function countries() {

		//$this->checkPlugin();

		$this->load->model('localisation/country');
	
		$json = array('success' => true, 'countries' => array());

		/*check category id parameter*/
		/*if (isset($this->request->get['id'])) {
			$country_id = $this->request->get['id'];
		} else {
			$country_id = 0;
		}*/

		$countries = $this->model_localisation_country->getCountries();

		foreach ($countries as $country) {

			$json['countries'][] = array(
					'id'			=> $country['country_id'],
					'name'			=> $country['name']
					//'iso_code_2'	=> $country['iso_code_2'],
					//'iso_code_3'	=> $country['iso_code_3'],
					//'address_format'		=> $country['address_format'],
                                        //'postcode_required' => $country['postcode_required'],
                                        //'status' => $country['status']
			);
		}

		if ($this->debugIt) {
			echo '<pre>';
			print_r($json);
			echo '</pre>';
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}

	public function country() {

		//$this->checkPlugin();

		$this->load->model('localisation/country');
	
		$json = array('success' => true);

		/*check category id parameter*/
		if (isset($this->request->get['id'])) {
			$country_id = $this->request->get['id'];
		} else {
			$country_id = 0;
		}

		$country = $this->model_localisation_country->getCountry($country_id);


		$json['country'] = array(
		    'id'			=> $country['country_id'],
		    'name'			=> $country['name'],
		    'iso_code_2'	=> $country['iso_code_2'],
		    'iso_code_3'	=> $country['iso_code_3'],
		    'address_format'		=> $country['address_format'],
                    'postcode_required' => $country['postcode_required'],
                    'status' => $country['status']
		);

		if ($this->debugIt) {
			echo '<pre>';
			print_r($json);
			echo '</pre>';
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}
	
	public function categories() {
		//$this->init();
		$this->load->model('catalog/category');
		$json = array('success' => true);

		# -- $_GET params ------------------------------
		
		if (isset($this->request->get['parent'])) {
			$parent = $this->request->get['parent'];
		} else {
			$parent = 0;
		}

		if (isset($this->request->get['level'])) {
			$level = $this->request->get['level'];
		} else {
			$level = 1;
		}

		# -- End $_GET params --------------------------


		$json['categories'] = $this->getCategoriesTree($parent, $level);

		if ($this->debug) {
			echo '<pre>';
			print_r($json);
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}

	public function category() {
		//$this->init();
		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$json = array('success' => true);

		# -- $_GET params ------------------------------
		
		if (isset($this->request->get['id'])) {
			$category_id = $this->request->get['id'];
		} else {
			$category_id = 0;
		}

		# -- End $_GET params --------------------------

		$category = $this->model_catalog_category->getCategory($category_id);
		
		$json['category'] = array(
			'id'                    => $category['category_id'],
			'name'                  => $category['name'],
			'description'           => $category['description'],
			'href'                  => $this->url->link('product/category', 'category_id=' . $category['category_id'])
		);

		if ($this->debug) {
			echo '<pre>';
			print_r($json);
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}

    public function product() {
		//$this->checkPlugin();

		$this->load->model('catalog/product');
        $product_id = $this->request->get['id'];
	
		$json = array();
		$product = $this->model_catalog_product->getProduct($product_id);
        $json['success'] = "TRUE";
        $json['product'] = $product;
		$this->response->setOutput(json_encode($json));

	}
	/*
	* Get products
	*/
	public function products() {

		//$this->checkPlugin();

		$this->load->model('catalog/product');
	
		$json = array('success' => true, 'products' => array());

		/*check category id parameter*/
		if (isset($this->request->get['category'])) {
			$category_id = $this->request->get['category'];
		} else {
			$category_id = 0;
		}

		$products = $this->model_catalog_product->getProducts(array(
			'filter_category_id'        => $category_id
		));

		foreach ($products as $product) {

			if ($product['image']) {
				$image = $product['image'];
			} else {
				$image = false;
			}

			if ((float)$product['special']) {
				$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$special = false;
			}

			$json['products'][] = array(
					'id'			=> $product['product_id'],
					'name'			=> $product['name'],
					'description'	=> $product['description'],
					'pirce'			=> $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'))),
					'href'			=> $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'thumb'			=> $image,
					'special'		=> $special,
					'rating'		=> $product['rating']
			);
		}

		if ($this->debugIt) {
			echo '<pre>';
			print_r($json);
			echo '</pre>';
		} else {
			$this->response->setOutput(json_encode($json));
		}
	}
		

	/*
	* Get orders
	*/
	public function orders() {
                $myfile = fopen("/tmp/log1", "w") or die("unable to open log file");
		$txt = "called orders";
		fwrite($myfile, $txt);
		//fclose($myfile);

		//$this->checkPlugin();
	
		$orderData['orders'] = array();

		$this->load->model('account/order');


		/*check offset parameter*/
		if (isset($this->request->get['offset']) && $this->request->get['offset'] != "" && ctype_digit($this->request->get['offset'])) {
			$offset = $this->request->get['offset'];
		} else {
			$offset 	= 0;
		}
		$offset 	= 0;

		/*check limit parameter*/
		if (isset($this->request->get['limit']) && $this->request->get['limit'] != "" && ctype_digit($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit 	= 10000;
		}
		$limit 	= 10;

		$txt1 = "got orders";
		fwrite($myfile, $txt1);
		fclose($myfile);
		
		/*get all orders of user*/
		//$results = $this->model_account_order->getAllOrders($offset, $limit);
		$results = $this->model_account_order->getOrders($offset, $limit);

		//failing here
		
		$orders = array();

                // not coming here


		if(count($results)){
			foreach ($results as $result) {

				$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
				$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);

				$orders[] = array(
						'order_id'		=> $result['order_id'],
						'order_status_id'		=> $result['order_status_id'],
						'name'			=> $result['firstname'] . ' ' . $result['lastname'],
						'status'		=> $result['status'],
						'date_added'	=> $result['date_added'],
						'products'		=> ($product_total + $voucher_total),
						'total'			=> $result['total'],
						'currency_code'	=> $result['currency_code'],
						'currency_value'=> $result['currency_value'],
				);
			}

			$json['success'] 	= true;
			$json['orders'] 	= $orders;
		}else {
			$json['success'] 	= false;
		}
		
		if ($this->debugIt) {
			echo '<pre>';
			print_r($json);
			echo '</pre>';

		} else {
			$this->response->setOutput(json_encode($json));
		}
	}	
	
	public function cart_add_bulk() {
                $cart_bulk = $this->request->post['products'];
                $input = stripslashes(html_entity_decode($cart_bulk));

                $result = json_decode($input, true);
                foreach ($result as $row) {
                        $this->cart->add($row[product_id], $row[quantity], "");
                }
	}

	public function cart_add() {
		//$this->load->language('api/cart');

		$json = array();


		if (isset($this->request->get['product_id'])) {
			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

			if ($product_info) {
				if (isset($this->request->get['quantity'])) {
					$quantity = $this->request->get['quantity'];
				} else {
					$quantity = 1;
				}

				if (isset($this->request->get['option'])) {
					$option = array_filter($this->request->get['option']);
				} else {
					$option = array();	
				}

				if (!isset($this->request->get['override']) || !$this->request->get['override']) {
					$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);

					foreach ($product_options as $product_option) {
						if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
							$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
						}
					}
				}

				if (!isset($json['error']['option'])) {
					$this->cart->add($this->request->get['product_id'], $quantity, $option);

					$json['success'] = $this->language->get('text_success');

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);					
				}
			} else {
				$json['error']['store'] = $this->language->get('error_store');
			}

			// Stock
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$json['error']['stock'] = $this->language->get('error_stock');
			}				
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}	

	public function cart_products() {
		//$this->load->language('api/cart');

		$json = array();

		// Products
		$json['product'] = array();

		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}	

			if ($product['minimum'] > $product_total) {
				$json['error']['product']['minimum'][] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
			}	

			$option_data = array();

			foreach ($product['option'] as $option) {
				$option_data[] = array(
					'product_option_id'       => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'name'                    => $option['name'],
					//'value'                   => $option['value'],
					'type'                    => $option['type']
				);
			}

			$json['product'][] = array(
				'product_id' => $product['product_id'],
				'name'       => $product['name'],
				'model'      => $product['model'], 
				'option'     => $option_data,
				'quantity'   => $product['quantity'],
				'stock'      => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
				'price'      => $product['price'],	
				'total'      => $product['total'],	
				'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
				'reward'     => $product['reward']				
			);
		}

		// Voucher
		$json['vouchers'] = array();

		if (!empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$json['voucher'][] = array(
					'code'             => $voucher['code'],
					'description'      => $voucher['description'],
					'code'             => $voucher['code'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'], 
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']    
				);
			}
		}

	$this->response->addHeader('Content-Type: application/json');
	$this->response->setOutput(json_encode($json));		
	}

	function cart_totals() {
		$json = array();		

		// Totals
		$this->load->model('setting/extension');

		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();

		$sort_order = array();

		$results = $this->model_setting_extension->getExtensions('total');

		foreach ($results as $key => $value) {
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}

		array_multisort($sort_order, SORT_ASC, $results);

		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('total/' . $result['code']);

				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
		}

		$sort_order = array();

		foreach ($total_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $total_data);

		$json['total'] = array();

		foreach ($total_data as $total) {
			$json['total'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'])
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}



	public function cart_update() {
		//$this->load->language('api/cart');

		$json = array();

		$this->cart->update($this->request->get['product_key'], $this->request->get['quantity']);

		$json['success'] = $this->language->get('text_success');

		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['reward']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}


	public function cart_remove() {
		$this->load->language('api/cart');

		$json = array();

		// Remove
		if (isset($this->request->get['product_key'])) {
			$this->cart->remove($this->request->get['product_key']);

			$json['success'] = $this->language->get('text_success');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	public function cart_clear() {

		$json = array();
		$this->cart->clear();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	private function getCategoriesTree($parent = 0, $level = 1) {
		$this->load->model('catalog/category');
		$this->load->model('tool/image');
		
		$result = array();

		$categories = $this->model_catalog_category->getCategories($parent);

		if ($categories && $level > 0) {
			$level--;

			foreach ($categories as $category) {

				if ($category['image']) {
					$image = $this->model_tool_image->resize($category['image'], $this->config->get('config_image_category_width'), $this->config->get('config_image_category_height'));
				} else {
					$image = false;
				}

				$result[] = array(
					'category_id'   => $category['category_id'],
					'parent_id'     => $category['parent_id'],
					'name'          => $category['name'],
					'image'         => $image,
					'href'          => $this->url->link('product/category', 'category_id=' . $category['category_id']),
					'categories'    => $this->getCategoriesTree($category['category_id'], $level)
				);
			}

			return $result;
		}
	}

        /*
         * wishlist
         */
	public function wishlistAdd() {
		//$this->checkPlugin();
		$this->language->load('account/wishlist');
		
		$json = array();

		if (!isset($this->session->data['wishlist'])) {
			$this->session->data['wishlist'] = array();
		}
		
                /*		
		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}*/

		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		if ($product_info) {
			//if (!in_array($this->request->post['product_id'], $this->session->data['wishlist'])) {	
			if (!in_array($this->request->get['product_id'], $this->session->data['wishlist'])) {	
				//$this->session->data['wishlist'][] = $this->request->post['product_id'];
				$this->session->data['wishlist'][] = $this->request->get['product_id'];
			}

                        // DUMMY test
                        //$this->session->data['customer_id'] = 123456;
                        //$this->customer->customer_id = 1;
			 
			if ($this->customer->isLogged()) {			
				//$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
                                //$json['success'] = "darun bepar";
			} else {
				//$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
				$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				
			}
			
			$json['total'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}	
		
		$this->response->setOutput(json_encode($json));
	}	

	public function wishlistView() {
		//$this->checkPlugin();
		$this->language->load('account/wishlist');
		
		$json = array();

		/*if (!isset($this->session->data['wishlist'])) {
                        $json['success'] = "nothing in wishlist"; 
		}*/
		
		$this->load->model('catalog/product');
                $items = $this->session->data['wishlist'];

                $json['products'] = array();

                foreach($items as $item) {
		
			$product_info = $this->model_catalog_product->getProduct($item);
			$json['products'][] = array(
					'id' => $item,
                                        'name' => $product_info['name']
			);
                }
		
		$this->response->setOutput(json_encode($json));
	}	

        // DUMMY function
	public function wishlistUnset() {
                unset($this->session->data['wishlist']);
                $this->session->data['wishlist'] = array();
        }

	public function wishlistDel() {
		//$this->checkPlugin();
		$this->language->load('account/wishlist');
		
		$json = array();

		/*if (!isset($this->session->data['wishlist'])) {
                        $json['success'] = "nothing in wishlist"; 
		}*/

		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$product_id = 0;
		}
		
		$this->load->model('catalog/product');
                $items = $this->session->data['wishlist'];

                //$json['products'] = array();

                $cnt = 0;
                foreach($items as $item) {

                        if ($item == $product_id) {
                            unset($items[$cnt]);
                        }
                        $cnt++;
                }

                unset($this->session->data['wishlist']);
		$this->session->data['wishlist'] = array();

                foreach($items as $item) {
			if (!in_array($product_id, $this->session->data['wishlist'])) {	
				$this->session->data['wishlist'][] = $item;
			}
		}

		//$this->session->data['wishlist'] = $items;

                $json['success'] = "product removed from whishlist";
		
		$this->response->setOutput(json_encode($json));
	}	

	public function customerLogin() {
                $json = array();
                if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
                    //change this to post
                    if (isset($this->request->post['email'])) {
                        $email_id = $this->request->post['email'];
                    } else {
                        $email_id = "";
                    }

                    if (isset($this->request->post['password'])) {
                        $passwd = $this->request->post['password'];
                    } else {
                        $passwd = "";
                    }

                    if ($this->customer->login($email_id, $passwd, false) == true) {
                        $json['success'] = "TRUE";
                        $json['customer_id'] = $this->customer->getId();
                    } else {
                        $json['success'] = "FALSE";
                    }
                }
                // we have to return the customer_id in return
                $this->response->setOutput(json_encode($json));

        }

	public function customerLogout() {
                $json = array();

                if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
                    $this->customer->logout();
                    $json['success'] = "TRUE";
                }

                $this->response->setOutput(json_encode($json));
        }

	public function addNewCustomer() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$data=$this->request->post;
                        $this->load->model('account/customer');
			$data["fax"] = "0000";
			$data["company"] = "NA";
			$data["company_id"] = "0";
			$data["tax_id"] = "0";
			$data["address_2"] = "";
			$data["city"] = "";
			$data["postcode"] = "0";
			$data["country_id"] = "0";
			$data["zone_id"] = "0";

                        $customer_info = $this->model_account_customer->getCustomerByEmail($data["email"]);
                        if (!$customer_info) {
                                $this->model_account_customer->addCustomer($data);
                                $new_customer_info = $this->model_account_customer->getCustomerByEmail($data["email"]);
                                if ($new_customer_info && $new_customer_info['approved']) {
                                        if ($this->customer->login($data["email"], $data["password"], false) == true) {
                                                $json['success'] = "TRUE";
                                        }
                                } else {
                                        $json['success'] = "FALSE";
                                }
                        } else {
                                $json['success'] = "EXISTS";
                        }
                }
                
		$this->response->setOutput(json_encode($json));
	}	


	public function setShipping() {


		$this->language->load('checkout/checkout');
		
		$this->load->model('account/address');
	
                // Dummy	
		//$shipping_address = $this->model_account_address->getAddress(1);		
		//if ($this->customer->isLogged() && isset($this->session->data['shipping_address_id'])) {					
		if ($this->customer->isLogged()) { 
                        // get shipping_address_id from customer
			$this->load->model('account/customer');
			$customer_id = $this->customer->getId();
                        $customer = $this->model_account_customer->getCustomer($customer_id);
                        $this->session->data['shipping_address_id'] = $customer['address_id'];

			$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$shipping_address = $this->session->data['guest']['shipping'];
		}
		
		if (!empty($shipping_address)) {
			// Shipping Methods
			$quote_data = array();
			
			$this->load->model('setting/extension');
			
			$results = $this->model_setting_extension->getExtensions('shipping');
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);
					
					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address); 
		
					if ($quote) {
						$quote_data[$result['code']] = array( 
							'title'      => $quote['title'],
							'quote'      => $quote['quote'], 
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}
	
			$sort_order = array();
		  
			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $quote_data);
			
			$this->session->data['shipping_methods'] = $quote_data;
		}


		$this->language->load('checkout/checkout');
		
		$json = array();		
		
		// Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}
		
		// Validate if shipping address has been set.		
		$this->load->model('account/address');

                /* shipping_address is already present
		if ($this->customer->isLogged() && isset($this->session->data['shipping_address_id'])) {					
			$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$shipping_address = $this->session->data['guest']['shipping'];
		} */
		
		if (empty($shipping_address)) {								
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}
		
		// Validate cart has products and has stock.	
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');				
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
				
		if (!$json) {
			if (!isset($this->request->get['shipping_method'])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				$shipping = explode('.', $this->request->get['shipping_method']);
					
				if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {			
					$json['error']['warning'] = $this->language->get('error_shipping');
				}
			}
			
			if (!$json) {
				$shipping = explode('.', $this->request->get['shipping_method']);
				// important line	
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
				
				//$this->session->data['comment'] = strip_tags($this->request->post['comment']);
			}							
		}
		
		$this->response->setOutput(json_encode($json));	
	}


	public function setPayment() {
                        // Totals
                        $total_data = array();
                        $total = 0;
                        $taxes = $this->cart->getTaxes();

                        $this->load->model('setting/extension');

                        $sort_order = array();

                        $results = $this->model_setting_extension->getExtensions('total');

                        foreach ($results as $key => $value) {
                                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
                        }
                        
                        array_multisort($sort_order, SORT_ASC, $results);

                        foreach ($results as $result) {
                                if ($this->config->get($result['code'] . '_status')) {
                                        $this->load->model('total/' . $result['code']);

					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}

		// Payment Methods
                $this->load->model('account/address');

                //Dummy
		//$payment_address = $this->model_account_address->getAddress(1);     
                //if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
                if ($this->customer->isLogged()) {  
                        // get payment_address_id from customer
			$this->load->model('account/customer');
			$customer_id = $this->customer->getId();
                        $customer = $this->model_account_customer->getCustomer($customer_id);
                        $this->session->data['payment_address_id'] = $customer['address_id'];

                        $payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);     
                } elseif (isset($this->session->data['guest'])) {
                        $payment_address = $this->session->data['guest']['payment'];
                }

		if (!empty($payment_address)) {

		$method_data = array();
			
		$this->load->model('setting/extension');
			
		$results = $this->model_setting_extension->getExtensions('payment');
	
		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('payment/' . $result['code']);
					
				$method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total); 
					
				if ($method) {
					$method_data[$result['code']] = $method;
				}
			}
		}
						 
		$sort_order = array(); 
		  
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
	
		array_multisort($sort_order, SORT_ASC, $method_data);			
			
		$this->session->data['payment_methods'] = $method_data;			
		}

                // go ahead

		$this->language->load('checkout/checkout');
		
		$json = array();
		
		// Validate if payment address has been set.
		$this->load->model('account/address');

/*	
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
				
		if (empty($payment_address)) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}	*/	
		
		// Validate cart has products and has stock.			
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');				
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
											
		if (!$json) {
			if (!isset($this->request->get['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} else {
				if (!isset($this->session->data['payment_methods'][$this->request->get['payment_method']])) {
					$json['error']['warning'] = $this->language->get('error_payment');
				}
			}	
			
/*				
			if ($this->config->get('config_checkout_id')) {
				$this->load->model('catalog/information');
				
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
				
				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
*/
			
			if (!$json) {
				$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->get['payment_method']];
			  
				//$this->session->data['comment'] = strip_tags($this->request->post['comment']);
			}							
		}
		
		$this->response->setOutput(json_encode($json));
	}

        public function getPayment() {
            
		$this->response->setOutput(json_encode($this->session->data['payment_methods']));
		$this->response->setOutput(json_encode($this->session->data['shipping_methods']));
		//$this->response->setOutput(json_encode($this->session->data['payment_method']));
                // whole purpose is to set these 2 below variables
		//$this->response->setOutput(json_encode($this->session->data['payment_address_id']));
		//$this->response->setOutput(json_encode($this->session->data['shipping_address_id']));
		//$this->response->setOutput(json_encode($this->data['cod_order_status_id']));

                unset($this->session->data['payment_methods']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['payment_address_id']);
                unset($this->session->data['shipping_address_id']);

        }

	public function setShippingpost() {


		$this->language->load('checkout/checkout');
		
		$this->load->model('account/address');
	
		if ($this->customer->isLogged()) { 
                        // get shipping_address_id from customer
                        if (isset($this->request->post['shipping_address'])) {
				$shipping_address = $this->request->post['shipping_address'];
                        } else {
				$this->load->model('account/customer');
				$customer_id = $this->customer->getId();
				$customer = $this->model_account_customer->getCustomer($customer_id);
				$this->session->data['shipping_address_id'] = $customer['address_id'];

				$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
                        }
		} elseif (isset($this->session->data['guest'])) {
			$shipping_address = $this->session->data['guest']['shipping'];
		}
		
		if (!empty($shipping_address)) {
			// Shipping Methods
			$quote_data = array();
			
			$this->load->model('setting/extension');
			
			$results = $this->model_setting_extension->getExtensions('shipping');
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('shipping/' . $result['code']);
					
					$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address); 
		
					if ($quote) {
						$quote_data[$result['code']] = array( 
							'title'      => $quote['title'],
							'quote'      => $quote['quote'], 
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}
	
			$sort_order = array();
		  
			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $quote_data);
			
			$this->session->data['shipping_methods'] = $quote_data;
		}


		$this->language->load('checkout/checkout');
		
		$json = array();		
		
		// Validate if shipping is required. If not the customer should not have reached this page.
		if (!$this->cart->hasShipping()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}
		
		// Validate if shipping address has been set.		
		$this->load->model('account/address');

                /* shipping_address is already present
		if ($this->customer->isLogged() && isset($this->session->data['shipping_address_id'])) {					
			$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$shipping_address = $this->session->data['guest']['shipping'];
		} */
		
		if (empty($shipping_address)) {								
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}
		
		// Validate cart has products and has stock.	
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');				
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
				
		if (!$json) {
			if (!isset($this->request->post['shipping_method'])) {
				$json['error']['warning'] = $this->language->get('error_shipping');
			} else {
				$shipping = explode('.', $this->request->post['shipping_method']);
					
				if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {			
					$json['error']['warning'] = $this->language->get('error_shipping');
				}
			}
			
			if (!$json) {
				$shipping = explode('.', $this->request->post['shipping_method']);
				// important line	
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
				
				//$this->session->data['comment'] = strip_tags($this->request->post['comment']);
			}							
		}
		
		$this->response->setOutput(json_encode($json));	
	}

	public function setPaymentpost() {
                        // Totals
                        $total_data = array();
                        $total = 0;
                        $taxes = $this->cart->getTaxes();

                        $this->load->model('setting/extension');

                        $sort_order = array();

                        $results = $this->model_setting_extension->getExtensions('total');

                        foreach ($results as $key => $value) {
                                $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
                        }
                        
                        array_multisort($sort_order, SORT_ASC, $results);

                        foreach ($results as $result) {
                                if ($this->config->get($result['code'] . '_status')) {
                                        $this->load->model('total/' . $result['code']);

					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}

		// Payment Methods
                $this->load->model('account/address');

                if ($this->customer->isLogged()) {  
                        // get payment_address_id from customer
			if (isset($this->request->post['payment_address'])) {
				$payment_address = $this->request->post['payment_address'];
			} else {
				$this->load->model('account/customer');
				$customer_id = $this->customer->getId();
				$customer = $this->model_account_customer->getCustomer($customer_id);
				$this->session->data['payment_address_id'] = $customer['address_id'];

				$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);     
			}
                } elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
                }

		if (!empty($payment_address)) {

		$method_data = array();
			
		$this->load->model('setting/extension');
			
		$results = $this->model_setting_extension->getExtensions('payment');
	
		foreach ($results as $result) {
			if ($this->config->get($result['code'] . '_status')) {
				$this->load->model('payment/' . $result['code']);
					
				$method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total); 
					
				if ($method) {
					$method_data[$result['code']] = $method;
				}
			}
		}
						 
		$sort_order = array(); 
		  
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
	
		array_multisort($sort_order, SORT_ASC, $method_data);			
			
		$this->session->data['payment_methods'] = $method_data;			
		}

                // go ahead

		$this->language->load('checkout/checkout');
		
		$json = array();
		
		// Validate if payment address has been set.
		$this->load->model('account/address');

/*	
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
				
		if (empty($payment_address)) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}	*/	
		
		// Validate cart has products and has stock.			
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');				
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
											
		if (!$json) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} else {
				if (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
					$json['error']['warning'] = $this->language->get('error_payment');
				}
			}	
			
/*				
			if ($this->config->get('config_checkout_id')) {
				$this->load->model('catalog/information');
				
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
				
				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
*/
			
			if (!$json) {
				$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
			  
				//$this->session->data['comment'] = strip_tags($this->request->post['comment']);
			}							
		}
		
		$this->response->setOutput(json_encode($json));
	}



	public function checkoutpost() {
		$redirect = '';
                $json = array();

		
		if ($this->cart->hasShipping()) {
			// Validate if shipping address has been set.		
			$this->load->model('account/address');
                        $this->setShippingpost();
                        $this->setPaymentpost();

			if ($this->customer->isLogged()) {					
				if (isset($this->request->post['shipping_address'])) {
					$shipping_address = $this->request->post['shipping_address'];
					$txt = str_replace("%40", ",", $shipping_address);
					$txt1 = str_replace("%20", " ", $txt);
					$txt2 = str_replace("%30", "-", $txt1);
                                        $shipping_address_new = $txt2;
				} else if (isset($this->session->data['shipping_address_id'])) {
					$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
				}
			} elseif (isset($this->session->data['guest'])) {
				$shipping_address = $this->session->data['guest']['shipping'];
			}
			
			if (empty($shipping_address)) {								
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
                                $json['success'] = "FAIL: shipping address not set";
			}
			
			// Validate if shipping method has been set.	
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
                                $json['success'] = "checkout failed shipping method not set";
			}
		} else {
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}
		
		// Validate if payment address has been set.
		$this->load->model('account/address');
	
		if ($this->customer->isLogged()) {
			if (isset($this->request->post['payment_address'])) {
				$payment_address = $this->request->post['payment_address'];
				$txt = str_replace("%40", ",", $payment_address);
				$txt1 = str_replace("%20", " ", $txt);
				$txt2 = str_replace("%30", "-", $txt1);
				$payment_address_new = $txt2;
			} else if (isset($this->session->data['payment_address_id'])) {
				$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
			}
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
		
                // failing here		
		if (empty($payment_address)) {
			$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			$json['success'] = "FAIL: payment address not set";
		}			
		
		// Validate if payment method has been set.	
		if (!isset($this->session->data['payment_method'])) {
			$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			$json['success'] = "FAIL: payment method not set";
		}
					
		// Validate cart has products and has stock.	
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');				
			$json['success'] = "FAIL: no stock ";
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');
				$json['success'] = "FAIL: product minimum order not met";
				
				break;
			}				
		}
						
		if (!$redirect) {
			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();
			 
			$this->load->model('setting/extension');
			
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}
			
			$sort_order = array(); 
		  
			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $total_data);
	
			$this->language->load('checkout/checkout');
			
			$data = array();
			
			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');
			
			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');		
			} else {
				$data['store_url'] = HTTP_SERVER;	
			}
			
			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				$data['fax'] = $this->customer->getFax();
			
				$this->load->model('account/address');
				$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
			} elseif (isset($this->session->data['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$data['firstname'] = $this->session->data['guest']['firstname'];
				$data['lastname'] = $this->session->data['guest']['lastname'];
				$data['email'] = $this->session->data['guest']['email'];
				$data['telephone'] = $this->session->data['guest']['telephone'];
				$data['fax'] = $this->session->data['guest']['fax'];
				
				$payment_address = $this->session->data['guest']['payment'];
			}
	
			if ($this->customer->isLogged()) {	
				if (isset($this->request->post['payment_address'])) {	
					$data['payment_firstname'] = $data['firstname'];
					$data['payment_lastname'] = $data['lastname'];	
					$data['payment_address_1'] = $payment_address_new;

					$data['payment_company'] = "";	
					$data['payment_company_id'] = "";	
					$data['payment_tax_id'] = "";	
					$data['payment_address_2'] = "";
					$data['payment_city'] = "";
					$data['payment_postcode'] = "";
					$data['payment_zone'] = "";
					$data['payment_zone_id'] = "";
					$data['payment_country'] = "";
					$data['payment_country_id'] = "";
					$data['payment_address_format'] = "";
				} else {
					$data['payment_firstname'] = $payment_address['firstname'];
					$data['payment_lastname'] = $payment_address['lastname'];	
					$data['payment_company'] = $payment_address['company'];	
					$data['payment_company_id'] = $payment_address['company_id'];	
					$data['payment_tax_id'] = $payment_address['tax_id'];	
					$data['payment_address_1'] = $payment_address['address_1'];
					$data['payment_address_2'] = $payment_address['address_2'];
					$data['payment_city'] = $payment_address['city'];
					$data['payment_postcode'] = $payment_address['postcode'];
					$data['payment_zone'] = $payment_address['zone'];
					$data['payment_zone_id'] = $payment_address['zone_id'];
					$data['payment_country'] = $payment_address['country'];
					$data['payment_country_id'] = $payment_address['country_id'];
					$data['payment_address_format'] = $payment_address['address_format'];
				}
			}  elseif (isset($this->session->data['guest'])) { 
				$data['payment_firstname'] = $this->session->data['guest']['firstname'];
				$data['payment_lastname'] = "";	
				$data['payment_address_1'] = $this->session->data['guest']['payment'];
				$data['payment_company'] = "";	
				$data['payment_company_id'] = "";	
				$data['payment_tax_id'] = "";	
				$data['payment_address_2'] = "";
				$data['payment_city'] = "";
				$data['payment_postcode'] = $this->session->data['guest']['pin'];
				$data['payment_zone'] = "";
				$data['payment_zone_id'] = "";
				$data['payment_country'] = "";
				$data['payment_country_id'] = "";
				$data['payment_address_format'] = "";
			}
		
			if (isset($this->session->data['payment_method']['title'])) {
				$data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}
			
			if (isset($this->session->data['payment_method']['code'])) {
				$data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$data['payment_code'] = '';
			}
						
			if ($this->cart->hasShipping()) {
				if ($this->customer->isLogged()) {
					$this->load->model('account/address');
					$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);	
				} elseif (isset($this->session->data['guest'])) {
					$shipping_address = $this->session->data['guest']['shipping'];
				}			
			
				if ($this->customer->isLogged()) {	
					if (isset($this->request->post['shipping_address'])) {	
						$data['shipping_firstname'] = $data['firstname'];
						$data['shipping_lastname'] = $data['lastname'];	
						$data['shipping_address_1'] = $shipping_address_new;

						$data['shipping_company'] = "";	
						$data['shipping_address_2'] = "";
						$data['shipping_city'] = "";
						$data['shipping_postcode'] = "";
						$data['shipping_zone'] = "";
						$data['shipping_zone_id'] = "";
						$data['shipping_country'] = "";
						$data['shipping_country_id'] = "";
						$data['shipping_address_format'] = "";
					} else {
						$data['shipping_firstname'] = $shipping_address['firstname'];
						$data['shipping_lastname'] = $shipping_address['lastname'];	
						$data['shipping_company'] = $shipping_address['company'];	
						$data['shipping_address_1'] = $shipping_address['address_1'];
						$data['shipping_address_2'] = $shipping_address['address_2'];
						$data['shipping_city'] = $shipping_address['city'];
						$data['shipping_postcode'] = $shipping_address['postcode'];
						$data['shipping_zone'] = $shipping_address['zone'];
						$data['shipping_zone_id'] = $shipping_address['zone_id'];
						$data['shipping_country'] = $shipping_address['country'];
						$data['shipping_country_id'] = $shipping_address['country_id'];
						$data['shipping_address_format'] = $shipping_address['address_format'];
					}
				}  elseif (isset($this->session->data['guest'])) { 
					$data['shipping_firstname'] = $this->session->data['guest']['firstname'];
					$data['shipping_lastname'] = "";	
					$data['shipping_address_1'] = $this->session->data['guest']['shipping'];

					$data['shipping_company'] = "";	
					$data['shipping_address_2'] = "";
					$data['shipping_city'] = "";
					$data['shipping_postcode'] = $this->session->data['guest']['pin'];
					$data['shipping_zone'] = "";
					$data['shipping_zone_id'] = "";
					$data['shipping_country'] = "";
					$data['shipping_country_id'] = "";
					$data['shipping_address_format'] = "";
				} 
			
				if (isset($this->session->data['shipping_method']['title'])) {
					$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}
				
				if (isset($this->session->data['shipping_method']['code'])) {
					$data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_code'] = '';
				}				
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';	
				$data['shipping_company'] = '';	
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
				$data['shipping_code'] = '';
			}
			
			$product_data = array();
		
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];	
					} else {
						$value = $this->encryption->decrypt($option['option_value']);
					}	
					
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],								   
						'name'                    => $option['name'],
						'value'                   => $value,
						'type'                    => $option['type']
					);					
				}
	 
				$product_data[] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				); 
			}
			
			// Gift Voucher
			$voucher_data = array();
			
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$voucher_data[] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],						
						'amount'           => $voucher['amount']
					);
				}
			}  
						
			$data['products'] = $product_data;
			$data['vouchers'] = $voucher_data;
			$data['totals'] = $total_data;
                        //Dummy
			//$data['comment'] = $this->session->data['comment'];
			$data['comment'] = "";
			$data['total'] = $total;
			
			if (isset($this->request->cookie['tracking'])) {
				$this->load->model('affiliate/affiliate');
				
				$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
				$subtotal = $this->cart->getSubTotal();
				
				if ($affiliate_info) {
					$data['affiliate_id'] = $affiliate_info['affiliate_id']; 
					$data['commission'] = ($subtotal / 100) * $affiliate_info['commission']; 
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
			
			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];
			
			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
			} else {
				$data['forwarded_ip'] = '';
			}
			
			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
			} else {
				$data['user_agent'] = '';
			}
			
			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
			} else {
				$data['accept_language'] = '';
			}
						
			$this->load->model('checkout/order');
			
			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);
                        $this->config->set('cod_order_status_id', 1);
                        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('cod_order_status_id'));
                        $order_id = $this->session->data['order_id'];
                        $json['success'] = sprintf("PASS: order_id %d", $order_id);
		}

		$this->response->setOutput(json_encode($json));
  	}


	public function guestCheckoutpost() {
                $guest = array();

                if (isset($this->request->post['payment_address_guest'])) {
			$p_addr = $this->request->post['payment_address_guest'];
			$txt = str_replace("%40", ",", $p_addr);
			$p_addr = str_replace("%20", " ", $txt);
			$txt = str_replace("%30", "-", $p_addr);
			$p_addr = $txt;
                        $guest['payment'] = $p_addr;
                }

                if (isset($this->request->post['shipping_address_guest'])) {
			$s_addr = $this->request->post['shipping_address_guest'];
			$txt = str_replace("%40", ",", $s_addr);
			$s_addr = str_replace("%20", " ", $txt);
			$txt = str_replace("%30", "-", $s_addr);
			$s_addr = $txt;
                        $guest['shipping'] = $s_addr;
                }

                if (isset($this->request->post['name_guest'])) {
			$n = $this->request->post['name_guest'];
			$txt = str_replace("%20", " ", $n);
                        $guest['firstname'] = $txt; 
                }

                if (isset($this->request->post['email_guest'])) {
			$guest['email'] = $this->request->post['email_guest'];
                }

                if (isset($this->request->post['phone_guest'])) {
			$guest['telephone'] = $this->request->post['phone_guest'];
                }

                if (isset($this->request->post['pin_guest'])) {
			$guest['pin'] = $this->request->post['pin_guest'];
		}

		$this->session->data['guest'] = $guest;

		$this->checkoutpost();
  	}

	public function checkout() {
		$redirect = '';
                $json = array();
		
		if ($this->cart->hasShipping()) {
			// Validate if shipping address has been set.		
			$this->load->model('account/address');
                        // Dummy added for checkout to work on its own when user is logged in
                        $this->setShipping();
                        $this->setPayment();

                        // Dummy	
			//$shipping_address = $this->model_account_address->getAddress(1);		
			if ($this->customer->isLogged() && isset($this->session->data['shipping_address_id'])) {					
				$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
			} elseif (isset($this->session->data['guest'])) {
				$shipping_address = $this->session->data['guest']['shipping'];
			}
			
			if (empty($shipping_address)) {								
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
                                $json['success'] = "FAIL: shipping address not set";
			}
			
			// Validate if shipping method has been set.	
			if (!isset($this->session->data['shipping_method'])) {
				$redirect = $this->url->link('checkout/checkout', '', 'SSL');
                                $json['success'] = "checkout failed shipping method not set";
			}
		} else {
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}
		
		// Validate if payment address has been set.
		$this->load->model('account/address');
	
                // Dummy 	
		//$payment_address = $this->model_account_address->getAddress(1);		
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
		
                // failing here		
		if (empty($payment_address)) {
			$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			$json['success'] = "FAIL: payment address not set";
		}			
		
		// Validate if payment method has been set.	
		if (!isset($this->session->data['payment_method'])) {
			$redirect = $this->url->link('checkout/checkout', '', 'SSL');
			$json['success'] = "FAIL: payment method not set";
		}
					
		// Validate cart has products and has stock.	
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$redirect = $this->url->link('checkout/cart');				
			$json['success'] = "FAIL: no stock ";
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');
				$json['success'] = "FAIL: product minimum order not met";
				
				break;
			}				
		}
						
		if (!$redirect) {
			$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();
			 
			$this->load->model('setting/extension');
			
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}
			
			$sort_order = array(); 
		  
			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}
	
			array_multisort($sort_order, SORT_ASC, $total_data);
	
			$this->language->load('checkout/checkout');
			
			$data = array();
			
			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');
			
			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');		
			} else {
				$data['store_url'] = HTTP_SERVER;	
			}
			
			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				$data['fax'] = $this->customer->getFax();
			
				$this->load->model('account/address');
			        //Dummy	
				$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
				//$payment_address = $this->model_account_address->getAddress(1);
			} elseif (isset($this->session->data['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
				$data['firstname'] = $this->session->data['guest']['firstname'];
				$data['lastname'] = $this->session->data['guest']['lastname'];
				$data['email'] = $this->session->data['guest']['email'];
				$data['telephone'] = $this->session->data['guest']['telephone'];
				$data['fax'] = $this->session->data['guest']['fax'];
				
				$payment_address = $this->session->data['guest']['payment'];
			}
			
			$data['payment_firstname'] = $payment_address['firstname'];
			$data['payment_lastname'] = $payment_address['lastname'];	
			$data['payment_company'] = $payment_address['company'];	
			$data['payment_company_id'] = $payment_address['company_id'];	
			$data['payment_tax_id'] = $payment_address['tax_id'];	
			$data['payment_address_1'] = $payment_address['address_1'];
			$data['payment_address_2'] = $payment_address['address_2'];
			$data['payment_city'] = $payment_address['city'];
			$data['payment_postcode'] = $payment_address['postcode'];
			$data['payment_zone'] = $payment_address['zone'];
			$data['payment_zone_id'] = $payment_address['zone_id'];
			$data['payment_country'] = $payment_address['country'];
			$data['payment_country_id'] = $payment_address['country_id'];
			$data['payment_address_format'] = $payment_address['address_format'];
		
			if (isset($this->session->data['payment_method']['title'])) {
				$data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}
			
			if (isset($this->session->data['payment_method']['code'])) {
				$data['payment_code'] = $this->session->data['payment_method']['code'];
			} else {
				$data['payment_code'] = '';
			}
						
			if ($this->cart->hasShipping()) {
                                //Dummy 
				//$shipping_address = $this->model_account_address->getAddress(1);	
				if ($this->customer->isLogged()) {
					$this->load->model('account/address');
				        //Dummy	
					$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);	
					//$shipping_address = $this->model_account_address->getAddress(1);	
				} elseif (isset($this->session->data['guest'])) {
					$shipping_address = $this->session->data['guest']['shipping'];
				}			
				
				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];	
				$data['shipping_company'] = $shipping_address['company'];	
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];
				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];
			
				if (isset($this->session->data['shipping_method']['title'])) {
					$data['shipping_method'] = $this->session->data['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}
				
				if (isset($this->session->data['shipping_method']['code'])) {
					$data['shipping_code'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_code'] = '';
				}				
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';	
				$data['shipping_company'] = '';	
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
				$data['shipping_code'] = '';
			}
			
			$product_data = array();
		
			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['option_value'];	
					} else {
						$value = $this->encryption->decrypt($option['option_value']);
					}	
					
					$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'option_id'               => $option['option_id'],
						'option_value_id'         => $option['option_value_id'],								   
						'name'                    => $option['name'],
						'value'                   => $value,
						'type'                    => $option['type']
					);					
				}
	 
				$product_data[] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']
				); 
			}
			
			// Gift Voucher
			$voucher_data = array();
			
			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$voucher_data[] = array(
						'description'      => $voucher['description'],
						'code'             => substr(md5(mt_rand()), 0, 10),
						'to_name'          => $voucher['to_name'],
						'to_email'         => $voucher['to_email'],
						'from_name'        => $voucher['from_name'],
						'from_email'       => $voucher['from_email'],
						'voucher_theme_id' => $voucher['voucher_theme_id'],
						'message'          => $voucher['message'],						
						'amount'           => $voucher['amount']
					);
				}
			}  
						
			$data['products'] = $product_data;
			$data['vouchers'] = $voucher_data;
			$data['totals'] = $total_data;
                        //Dummy
			//$data['comment'] = $this->session->data['comment'];
			$data['comment'] = "";
			$data['total'] = $total;
			
			if (isset($this->request->cookie['tracking'])) {
				$this->load->model('affiliate/affiliate');
				
				$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
				$subtotal = $this->cart->getSubTotal();
				
				if ($affiliate_info) {
					$data['affiliate_id'] = $affiliate_info['affiliate_id']; 
					$data['commission'] = ($subtotal / 100) * $affiliate_info['commission']; 
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
			
			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];
			
			if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];	
			} elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
				$data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];	
			} else {
				$data['forwarded_ip'] = '';
			}
			
			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];	
			} else {
				$data['user_agent'] = '';
			}
			
			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];	
			} else {
				$data['accept_language'] = '';
			}
						
			$this->load->model('checkout/order');
			
			$this->session->data['order_id'] = $this->model_checkout_order->addOrder($data);
                        $this->config->set('cod_order_status_id', 1);
                        $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('cod_order_status_id'));
                        $order_id = $this->session->data['order_id'];
                        $json['success'] = sprintf("PASS: order_id %d", $order_id);
		}
		
		$this->response->setOutput(json_encode($json));
  	}

	private function checkPlugin() {

		$json = array("success"=>false);

		/*check rest api is enabled*/
		if (!$this->config->get('rest_api_status')) {
			$json["error"] = 'API is disabled. Enable it!';
		}
		
		/*validate api security key*/
		if ($this->config->get('rest_api_key') && (!isset($this->request->get['key']) || $this->request->get['key'] != $this->config->get('rest_api_key'))) {
			$json["error"] = 'Invalid secret key';
		}
		
		if(isset($json["error"])){
			$this->response->addHeader('Content-Type: application/json');
			echo(json_encode($json));
			exit;
		}else {
			$this->response->setOutput(json_encode($json));			
		}	
	}	

}
