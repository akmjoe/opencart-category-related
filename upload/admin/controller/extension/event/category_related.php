<?php
class controllerExtensionEventCategoryRelated extends Controller {
	
	public function view(&$view, &$data, &$output) {// triggered before view category form
                if(!$this->config->get('module_category_related_status')) {
                    // module disabled
                    return;
                }
                // get category_related data
		$this->load->model('catalog/product');
		if(isset($this->request->get['category_id']) && $this->request->get['category_id']) {// get related products
			$this->load->model('extension/module/category_related');
			$info = $this->model_extension_module_category_related->getRelated($this->request->get['category_id']);
		} else {
			$info = array();
		}
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			// failed save - override with post
			$info = $this->request->post['product_related'];
		}

		$this->language->load('extension/module/category_related');
                // set data parameters
                $data['category_related_key'] = $this->config->get('module_category_related_key');
                $data['entry_related'] = $this->language->get('entry_related');
                $data['help_related'] = $this->language->get('help_related');
                foreach($info as $related) {
			$result = $this->model_catalog_product->getProduct($related);
                        $data['product_related'][$related] = $result[$this->config->get('module_category_related_key')];
                }
                
                $data['header'] .= $this->load->view('extension/module/category_related_category', $data);
	}
	
	public function save(&$route, &$data, &$output = null) {
		if((int)$output) {
			$id = $output;
			$temp = $data[0];
		} else {
			$temp = $data[1];
			$id = $data[0];
		}
		$this->load->model('extension/module/category_related');
		
		$this->model_extension_module_category_related->saveRelated($id, $temp);
	}
	
}
