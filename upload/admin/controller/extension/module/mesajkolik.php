<?php
class ControllerExtensionModuleMesajkolik extends Controller {


    private $error = [];

    public function index(){

      $this->load->language("extension/module/mesajkolik");
      $this->document->setTitle($this->language->get("heading_title"));

      $this->load->model('setting/setting');
      $this->load->model('design/layout');
      if (($this->request->server["REQUEST_METHOD"] == "POST") && $this->validate()) {
          $this->model_setting_setting->editSetting('mesajkolik', $this->request->post);
          $this->session->data["success"] = $this->language->get("text_success");
          if (isset($this->request->post['sayfayi_yenile']) and $this->request->post['sayfayi_yenile']) {
              $this->response->redirect($this->url->link('extension/module/mesajkolik', 'user_token=' . $this->session->data['user_token'], true));
          } else {
              $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
          }
      }else if (($this->request->server["REQUEST_METHOD"] == "POST")) {
        $this->model_setting_setting->editSetting('mesajkolik', $this->request->post);
        $data['cemil'] = "Method Posttur".$this->request->post['mesajkolik_user'].$this->request->post['mesajkolik_pass'];
      } else {
        $data['cemil'] = "Sayfa Yeni AÇılmıştır";
      }

      if (isset($this->error["warning"])) {
          $data["error_warning"] = $this->error["warning"];
      } else {
          $data["error_warning"] = "";
      }

      $data['breadcrumbs'] = array();

      $data['breadcrumbs'][] = array(
          'text' => $this->language->get('text_home'),
          'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
          'separator' => false
      );

      $data['breadcrumbs'][] = array(
          'text' => "Eklentiler",
          'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
          'separator' => ' :: '
      );

      $data['breadcrumbs'][] = array(
          'text' => $this->language->get('heading_title'),
          'href' => $this->url->link('extension/module/mesajkolik', 'user_token=' . $this->session->data['user_token'], true),
          'separator' => ' :: '
      );

      $data['action'] = $this->url->link('extension/module/mesajkolik', 'user_token=' . $this->session->data['user_token'], true);
      $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
      $data['user_token'] = $this->session->data['user_token'];



      $data['version'] = VERSION;

      $data["header"] = $this->load->controller("common/header");
      $data["column_left"] = $this->load->controller("common/column_left");
      $data["footer"] = $this->load->controller("common/footer");
      //gelen tüm postları key ve valueleri ile birlikte alıyoruz ve oc $data[] arrayına kaydediyoruz
      $searcfor = array('mesajkolik_status','mesajkolik_auto_1_toggle','mesajkolik_auto_1_phones','mesajkolik_auto_1_message',
      'mesajkolik_auto_2_toggle','mesajkolik_auto_2_message','mesajkolik_auto_3_toggle','mesajkolik_auto_3_phones',
      'mesajkolik_auto_3_message','mesajkolik_auto_4_toggle','mesajkolik_auto_4_message','mesajkolik_auto_5_toggle',
      'mesajkolik_auto_5_message','mesajkolik_auto_6_toggle','mesajkolik_auto_6_phones','mesajkolik_auto_6_message',
      'mesajkolik_auto_7_message','mesajkolik_lastgroup','mesajkolik_lastgroup_toggle','mesajkolik_optionstab',
      'mesajkolik_user','mesajkolik_pass','sayfayi_yenile','mesajkolik_header');
      for ($i = 0; $i < count($searcfor); $i++)
      {
          if (isset($this->request->post[$searcfor[$i]])) {
              $data[$searcfor[$i]] = $this->request->post[$searcfor[$i]];
          } elseif ($this->config->get($searcfor[$i])) {
              $data[$searcfor[$i]] = $this->config->get($searcfor[$i]);
          } else {
              $data[$searcfor[$i]] = '';
          }
          // $allvals = "'".$allvals."'".$searcfor[$i];
      }

      $mesajkolik = new MesajkolikApi($data['mesajkolik_user'], $data['mesajkolik_pass']);

      $balance = $mesajkolik->getBalance();
      $check_login = $balance!==false;
      $headers = $mesajkolik->getHeaders();
      $tab = empty($data['mesajkolik_optionstab']) ? 'info' : $data['mesajkolik_optionstab'];
      if (!$check_login) {
        $data['mesajkolik_optionstab'] = 'settings';
        $tab = 'settings';
      }
      $data['check_login'] = $check_login;
      $data['balance'] = $balance;
      $data['headers'] = $headers;
      $data['cemil'] = $check_login."-".$balance;
      $allcustomers= $this->getAllCustomers();
      $data['customers'] = $allcustomers;
      $data['tab'] = $tab;
      $this->response->setOutput($this->load->view("extension/module/mesajkolik", $data));
    }

    function mesajkolik_label_clear($message){
        return str_replace([
          '[uye_adi]',
          '[uye_soyadi]',
          '[uye_telefon]',
          '[uye_eposta]',
          '[siparis_durum]',
          '[siparis_no]',
          '[siparis_tutar]'
        ], [
          $this->request->post['firstname'],
          $this->request->post['lastname'],
          $this->request->post['telephone'],
          $this->request->post['email'],
          $this->request->post['password']
        ], $message);
    }

    private function addModule(){
      $this->load->model('setting/module');
      $this->model_setting_module->addModule('mesajkolik', self::DEFAULT_MODULE_SETTINGS);
      return $this->db->getLastId();
    }

    protected function editModule($module_id) {
      $data = [];
      $htmlOutput = $this->load->view('extension/module/mesajkolik', $data);
      $this->response->setOutput($htmlOutput);
    }

    public function validate() {
      if (!$this->user->hasPermission('modify', 'extension/module/mesajkolik')) {
          $this->error['warning'] = $this->language->get('error_permission');
      }
      return !$this->error;
    }

    public function getAllCustomers(){
      $this->load->model('customer/customer');
      $results = $this->model_customer_customer->getCustomers();
      $contacts = [];
      foreach ($results as $result) {
          $obj = new stdClass();
          $obj->id = $result['customer_id'];
          $obj->status = $result['status'];
          $obj->name = $result['firstname'];
          $obj->lastname = $result['lastname'];
          $obj->telephone = str_replace('+','',$result['telephone']);
          $obj->email = $result['email'];
          array_push($contacts, $obj);
      }
      $data['customers'] = $contacts;
      return $data['customers'];
    }

    public function sendPrivateMessage(){
      $mesajkolik = new MesajkolikApi($this->config->get('mesajkolik_user'), $this->config->get('mesajkolik_pass'));

      if ( (isset($this->request->post['gsm']) && isset($this->request->post['message'])) && ( !empty($this->request->post['gsm']) && !empty($this->request->post['message']) ) ) {
        $send = $mesajkolik->sendsms(str_replace('+', '', $this->request->post['gsm']), $this->request->post['message'], $this->config->get('mesajkolik_header'));
        echo json_encode($send);
      }else {
        echo json_encode(['result' => false, 'message' => 'SMS gönderebilmek için numara seçmeli ve bir mesaj içeriği girmelisiniz.']);
      }
    }

    public function pluginStat(){

      $this->load->model('setting/setting');
      if (isset($this->request->post['mesajkolik_status'])) {
        $val = $this->request->post['mesajkolik_status'];
        $this->model_setting_setting->editSettingValue('mesajkolik', 'mesajkolik_status', $val);
        echo json_encode(['result' => $this->request->post['mesajkolik_status']]);
      }else {
        echo json_encode(['result' => false]);
      }

    }

    public function sendBulkSms(){
      $mesajkolik = new MesajkolikApi($this->config->get('mesajkolik_user'), $this->config->get('mesajkolik_pass'));

      if ( (isset($this->request->post['id']) && isset($this->request->post['message'])) && ( !empty($this->request->post['id']) && !empty($this->request->post['message']) ) ) {
        $gsmpost = $this->request->post['id'];
        $user = is_array($this->request->post['id']) ? array_filter($this->request->post['id']) : array_filter(explode(',', $this->request->post['id']));
        $sended = [];
        $sms = [];

        $this->load->model('customer/customer');

        foreach ($user as $key) {

          $customer_info = $this->model_customer_customer->getCustomer($key);

          $billing_phone = $customer_info['telephone'];
          if(!empty($billing_phone) && !in_array($billing_phone,$sended)){
            $sended[] = $billing_phone;
            $sms[] = [
              'gsm' => str_replace('+', '', $billing_phone),
              'message' => str_replace(['[uye_adi]','[uye_soyadi]'], [$customer_info['firstname'],$customer_info['lastname']], $this->request->post['message'])
            ];
          }
        }
        $send = $mesajkolik->advancedsms($sms, $this->config->get('mesajkolik_header'));
        echo json_encode($send);

      }else {
        echo json_encode(['result' => false, 'message' => 'Toplu sms gönderebilmek için numara seçmeli ve bir mesaj içeriği girmelisiniz.']);
      }

    }

    public function backupAllContact(){

      $defaultGroupName = 'Opencart';
      if ( (isset($this->request->post['mesajkolik_lastgroup']) || isset($this->request->post['mesajkolik_lastgroup_toggle'])) &&
          ( !empty($this->request->post['mesajkolik_lastgroup']) || !empty($this->request->post['mesajkolik_lastgroup_toggle']) ) ) {

            //1
            $this->load->model('customer/customer');
            $results = $this->model_customer_customer->getCustomers();
            $contacts = [];

            $mesajkolik = new MesajkolikApi($this->config->get('mesajkolik_user'), $this->config->get('mesajkolik_pass'));
            $defaultGroupName = $this->request->post['mesajkolik_lastgroup'];
            $groupadd = $mesajkolik->groupadd($defaultGroupName);
            //1

            if($groupadd->result == 0){
              echo json_encode(['result' => false, 'message' => 'Grup eklenemedi.']);
              return false;
            }

            $groupid = ((array)($groupadd->data))[0]->id;

            foreach ($results as $result) {
                $obj = new stdClass();
                $obj->name = $result['firstname'];
                $obj->surname = $result['lastname'];
                $obj->gsm = str_replace('+','',$result['telephone']);
                $obj->group_id = $groupid;
                array_push($contacts, $obj);
            }

            $add = $mesajkolik->personadd($contacts, $groupid);
            echo json_encode(['result'=>'success','message'=> ''.$defaultGroupName.' Gurubuna '.count($contacts).' numara aktarıldı.']);
      }else {
        echo json_encode(['result' => false, 'message' => 'Toplu sms gönderebilmek için numara seçmeli ve bir mesaj içeriği girmelisiniz.']);
      }

    }

    public function install(){
      $this->load->model('setting/setting');
      $this->model_setting_setting->editSetting('mesajkolik', ['mesajkolik_status'=>1]);
    }

    public function uninstall(){
      $this->load->model('setting/setting');
      $this->model_setting_setting->deleteSetting('mesajkolik');
    }
}
