<?php


class CRM_Event_Cart_BAO_EventInCart extends CRM_Event_Cart_DAO_EventInCart implements
  ArrayAccess
{
  public $assocations_loaded = false;
  public $event;
  public $event_cart;
  public $location = null;
  public $participants = array( );

  function __construct( )
  {
	parent::__construct( );
  }

  public function add_participant($participant)
  {
    $this->participants[$participant->id] = $participant;
  }

  public static function create( $params )
  {
	$transaction = new CRM_Core_Transaction( );
	$event_in_cart = new CRM_Event_Cart_BAO_EventInCart( );
	$event_in_cart->copyValues( $params );
	$event_in_cart = $event_in_cart->save( );

	if ( is_a( $event_in_cart, 'CRM_Core_Error') ) {
	  $transaction->rollback( );
	  CRM_Core_Error::fatal( ts( 'There was an error creating an event_in_cart') );
	}

	$transaction->commit( );

	return $event_in_cart;
  }

  function delete()
  {
    $this->load_associations();
    $contacts_to_delete = array();
    foreach ($this->participants as $participant)
    {
        $defaults = array( );
        $params = array( 'id' => $participant->contact_id);
        $temporary_contact = CRM_Contact_BAO_Contact::retrieve( $params, $defaults );

        if ($temporary_contact->is_deleted) {
          $contacts_to_delete[$temporary_contact->id] = 1;
        }
        $participant->delete();
    }
    foreach (array_keys($contacts_to_delete) as $contact_id)
    {
        CRM_Contact_BAO_Contact::deleteContact($contact_id);
    }
    parent::delete();
  }

  public static function find_all_by_event_cart_id( $event_cart_id )
  {
	return self::find_all_by_params( array( 'event_cart_id' => $event_cart_id ) );
  }

  public static function find_all_by_params( $params )
  {
	$event_in_cart = new CRM_Event_Cart_BAO_EventInCart( );
	$event_in_cart->copyValues( $params );
	$result = array();
	if ( $event_in_cart->find( ) ) {
	  while ( $event_in_cart->fetch( ) ) {
		$result[$event_in_cart->event_id] = clone( $event_in_cart );
	  }
	}
	return $result;
  }

  public static function find_by_id( $id )
  {
	return self::find_by_params( array( 'id' => $id ) );
  }

  public static function find_by_params( $params )
  {
	$event_in_cart = new CRM_Event_Cart_BAO_EventInCart( );
	$event_in_cart->copyValues( $params );
	if ( $event_in_cart->find( true ) ) {
	  return $event_in_cart;
	} else {
	  return false;
	}
  }

  public function remove_participant_by_contact_id($contact_id) {
	$to_remove = array( );
	foreach ( $this->participants as $participant ) {
	      if ($participant->contact_id == $contact_id) {
		$to_remove[$participant->id] = 1;
		$participant->delete( );
	      }
	}
	$this->participants = array_diff_key( $this->participants, $to_remove );
  }

  public function get_participant_by_id($participant_id) {
    return $this->participants[$participant_id];
  }

  public function remove_participant_by_id($participant_id) {
    $this->get_participant_by_id($participant_id)->delete();
    unset($this->participants[$participant_id]);
  }

  static function part_key( $participant ) {
      return $participant->id;
  }

  public function load_associations( $event_cart = null ) {
	if ( $this->assocations_loaded ) {
	  return;
	}
	$this->assocations_loaded = true;
	$params = array( 'id' => $this->event_id );
	$defaults = array( );
	$this->event = CRM_Event_BAO_Event::retrieve( $params, $defaults );

	if ( $event_cart != null ) {
	  $this->event_cart = $event_cart;
	  $this->event_cart_id = $event_cart->id;
	} else {
	  $this->event_cart = CRM_Event_Cart_BAO_Cart::find_by_id( $this->event_cart_id);
	}

	$participants = CRM_Event_Cart_BAO_MerParticipant::find_all_by_event_and_cart_id($this->event_id, $this->event_cart->id);
	foreach ($participants as $participant) {
	    $participant->load_associations( );
	    $this->add_participant( $participant );
	}
  }

  public function load_location( ) {
	if ($this->location == null) {
	  $location_params = array( 'entity_id' => $this->event_id, 'entity_table' => 'civicrm_event' );
	  $this->location = CRM_Core_BAO_Location::getValues( $location_params, true );
	}
  }

  public function not_waiting_participants( ) {
	$result = array( );
	foreach ( $this->participants as $participant ) {
	  if ( !$participant->must_wait ) {
		$result[] = $participant;	
	  }
	}
	return $result;
  }

  public function num_not_waiting_participants( ) {
	return count( $this->not_waiting_participants( ) );
  }

  public function num_waiting_participants( ) {
	return count( $this->waiting_participants( ) );
  }


  public function offsetExists( $offset ) {
	return array_key_exists(array_merge($this->fields( ), array('main_conference_event_id')), $offset);
  }

  public function offsetGet( $offset ) {
	if ( $offset == 'event' ) {
	  return $this->event->toArray();
	}
	if ( $offset == 'id' ) {
	  return $this->id;
	}
	if ( $offset == 'main_conference_event_id' ) {
	  return $this->main_conference_event_id;
	}
	$fields =& $this->fields( );
	return $fields[$offset];
  }

  public function offsetSet( $offset, $value ) {
  }

  public function offsetUnset( $offset ) {
  }

  public function waiting_participants( ) {
	$result = array( );
	foreach ( $this->participants as $participant ) {
	  if ( $participant->must_wait ) {
		$result[] = $participant;	
	  }
	}
	return $result;
  }

  static function get_registration_link($event_id)
  {
      $cart = CRM_Event_Cart_BAO_Cart::find_or_create_for_current_session( );
      $cart->load_associations( );
      $event_in_cart = $cart->get_event_in_cart_by_event_id( $event_id );

      if ($event_in_cart) {
        return array(
          'label' => "Remove from Cart",
          'path' => 'civicrm/event/remove_from_cart',
          'query' => "reset=1&id={$event_id}",
        );
      } else {
        return array(
          'label' => "Add to Cart",
          'path' => 'civicrm/event/add_to_cart',
          'query' => "reset=1&id={$event_id}",
        );
      }
  }

  function is_parent_event()
  {
    return (null !== (CRM_Event_BAO_Event::get_sub_events($this->event_id)));
  }

  function is_child_event($parent_event_id = null)
  {
    if ($parent_event_id == null)
        return $this->event->parent_event_id;
    else
        return $this->event->parent_event_id == $parent_event_id;
  }
}
