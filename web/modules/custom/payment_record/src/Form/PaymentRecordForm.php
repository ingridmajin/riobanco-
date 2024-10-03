<?php

namespace Drupal\payment_record\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class PaymentRecordForm extends FormBase
{
  public function getFormId()
  {
    return "payment_record_form";

  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    //Campo para seleccionar el credito (referencia al nodo tipo creditos).
    $form['credit'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('seleccionar accionista'),
      '#target_type' => 'node',
      //'#selection_handler' => 'default',
      '#selection_settings' => [
        'target_bundles' => ['credits'],
        //'field' => 'field_shareholder_name',
      ],
      '#ajax' => [
        'callback' => '::updateCredits',
        'wrapper' => 'credit-info-wrapper',
      ],
    ];

    //Contenedor que se llamará con la informacion del credito seleccionado
    $form['credit_info'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'credit-info-wrapper'],
    ];

    //campo monto (solo lectura)
    $form['credit_info']['required_amount'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Monto del credito'),
      '#disabled' => TRUE,
    ];

    //Numero de cuotas ((solo lectura)).
    $form['credit_info']['fees'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Numeo de cuotas'),
      '#disabled' => TRUE,
    ];

    //Campo para la fecha de pago
    $form['fecha_pago'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de pago'),
      '#required' => TRUE,
    ];

    //Numero de cuotas a pagar.
    $form['numero_cuota'] = [
      '#type' => 'number',
      '#title' => $this->t('Numero de cuota a ṕagar'),
      '#required' => TRUE,
    ];
    //valor capital a pagar.
    $form['valor_capital'] = [
      '#type' => 'number',
      '#title' => $this->t('Valor capital a pagar'),
      '#required' => TRUE,
    ];

    //valor interes a pagar.
    $form['valor_interes'] = [
      '#type' => 'number',
      '#title' => $this->t('Valor interes a pagar'),
      '#required' => TRUE,
    ];

    //boton de envio.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Registrar pago'),
    ];

    //return $form;
    return [
      '#theme' => 'payment_record_form',
      '#title' => $this->t('Registrar pago'),
      '#form' => $form,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }
  /**
   * Funcion AJAX para actualizar la información del credito basado en el usuario seleccionado
   */
  public function updateCredits(array $form, FormStateInterface $form_state)
  {
    $nid= $form_state->getValue('credit');
    if ($nid) {
      //cargar el nodo de crdito seleccionado
      $credito = Node::load($nid);

      //siel nodo es valido y del tipo correcto, llenamos los campos.
      if ($credito && $credito->bundle()==='credits'){
        $form['credit_info']['Required amount']['#value'] = $credito->get('field_required_amount')->value;
        $form['credit_info']['Fees	']['#value'] = $credito->get('field_fess')->value;
      }

    }
    return $form['credit_info'];
  }

 /* public function validateForm(array &$form, FormStateInterface $form_state)
  {
    if (strlen($form_state->getValue('username')) <6) {
      $form_state->setErrorByName('username', 'Username is too short. please make sure your username lenght is more than 5');
    }
  }*/

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    //obtener y registrar los datos del formulario
    $credit = $form_state->getValue('credit');
    $fecha_pago = $form_state->getValue('registration_date');
    $numero_cuota = $form_state->getValue('fee');
    $valor_capital = $form_state->getValue('capital');
    $valor_interes = $form_state->getValue('interests');

   //cargar el credito relacionado (basado en el usuario)
    /*$creditos = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'payment_record',
        'nid' => $credit]);*/

   // $credito = reset($creditos);//tomar el primer credito asociado al usuario

    // crear un nuevo registro del pago de la cuota
    //if($credito) {
      $node = Node::create([
        'type' => 'payment_record',
        'title' => 'Payment record-'.$numero_cuota,
        'field_credit' => $credit,
        'field_registration_date' => $fecha_pago,
        'field_fess' => $numero_cuota,
        'field_capital' => $valor_capital,
        'field_interests' => $valor_interes,
        'status' => 1,
      ]);

      //guardar el nodo
      $node->save();

      //Mensaje de confirmación.
      \Drupal::messenger()->addMessage($this->t('Credito registrado correctamente.'));

    /*}else{
      //mandar un mensaje de error si no hay un credito asociado
      \Drupal::messenger()->addMessage($this->t('No tiene creditos asociados.'));
    }*/
  }
}
