<?php
class ControllerExtensionModuleCategoryRelated extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/category_related');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_category_related', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
                        $this->update();

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/category_related', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/category_related', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_category_related_status'])) {
			$data['module_category_related_status'] = $this->request->post['module_category_related_status'];
		} else {
			$data['module_category_related_status'] = $this->config->get('module_category_related_status');
		}

		if (isset($this->request->post['module_category_related_key'])) {
			$data['module_category_related_key'] = $this->request->post['module_category_related_key'];
		} else {
			$data['module_category_related_key'] = $this->config->get('module_category_related_key');
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/category_related', $data));
	}
	
	public function install() {
		// add db table
		$this->load->model('extension/module/category_related');
		$this->model_extension_module_category_related->install();
		// set up event handlers
		$this->update();
	}
        
        public function update() {
            // we just need to re-do the events
            $this->load->model('setting/event');
            $this->model_setting_event->deleteEventByCode('category_related');
            $this->model_setting_event->addEvent('category_related', 'admin/view/catalog/category_form/before', 'extension/event/category_related/view');
            $this->model_setting_event->addEvent('category_related', 'admin/model/catalog/category/addCategory/after', 'extension/event/category_related/save');
            $this->model_setting_event->addEvent('category_related', 'admin/model/catalog/category/editCategory/after', 'extension/event/category_related/save');
            $this->model_setting_event->addEvent('category_related', 'admin/model/catalog/category/deleteCategory/after', 'extension/event/category_related/delete');
            // Modify catalog page
            $this->model_setting_event->addEvent('category_related', 'catalog/model/catalog/product/getProductRelated/after', 'extension/event/category_related/productRelated');
        }
	
	public function uninstall() {
		// remove db table
		$this->load->model('extension/module/category_related');
		$this->model_extension_module_category_related->uninstall();
		// remove events
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('category_related');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/category_related')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		

		return !$this->error;
	}
}