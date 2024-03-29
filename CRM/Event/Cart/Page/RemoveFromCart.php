<?php


class CRM_Event_Cart_Page_RemoveFromCart extends CRM_Core_Page {
  function run( ) {
    $transaction = new CRM_Core_Transaction();
    $this->_id = CRM_Utils_Request::retrieve( 'id', 'Positive', $this, true );
    $cart = CRM_Event_Cart_BAO_Cart::find_or_create_for_current_session( );
    $cart->load_associations( );
    $event_in_cart = $cart->get_event_in_cart_by_event_id($this->_id);
    $removed_event = $cart->remove_event_in_cart($event_in_cart->id);
    $removed_event_title = $removed_event->event->title;
    CRM_Core_Session::setStatus( ts("<b>%1</b> has been removed from your cart.", array( 1 => $removed_event_title ) ) );
    $transaction->commit();
    return CRM_Utils_System::redirect( $_SERVER['HTTP_REFERER'] );
  }
}

?>
