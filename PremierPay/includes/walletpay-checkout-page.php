<?php
/**
 * Defaults for the walletpay checkout page.
 */

add_filter( 'woocommerce_gateway_description', 'walletpay_billing_phone_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'walletpay_billing_phone_fields_validation', 20, 1 );
// add_action( 'woocommerce_checkout_update_order_meta', 'walletpay_billing_phone_save_field' );
// add_action( 'woocommerce_admin_order_data_after_billing_address', 'walletpay_billing_phone_show_field_admin_order', 10, 1 );

/**
 * Check if the phone number for billing is filled.
 *
 * @param object $order Order Object.
 * @return void
 */
function walletpay_billing_phone_fields_validation( $order ) {

    if ( 'wallet_pay' === $_POST['payment_method'] ) {
    
        $walletpayerWalletID = $_POST['walletpayerWalletID'];
        // Error the Phone number
        if( ! isset( $walletpayerWalletID ) || empty( $walletpayerWalletID ) ) {
            wc_add_notice( 'Please enter the Phone Number for Billing (Format: 00252615080326 )', 'error' );
            
            return;
        }
    
        if( ! is_numeric( $walletpayerWalletID ) ) {
            wc_add_notice( 'Please enter the Phone Number with correct format e.g 00252615080326 )', 'error' );
            return;
        }
        
        if(strlen((string)$walletpayerWalletID)>9)
        {
              if(strlen((string)$walletpayerWalletID)==10 || strlen((string)$walletpayerWalletID)==12)
                    {
                            
                       if (substr($walletpayerWalletID, 0,2) == '06')
                            {
                                 $walletpayerWalletID = str_replace('06', '002526', $walletpayerWalletID);
                                 //echo $walletpayerWalletID;
                                    
                            } 
                       else if(substr($walletpayerWalletID, 0,3) == '252')
                            {
                             $walletpayerWalletID = str_replace('252', '002526', $walletpayerWalletID);
                             echo $walletpayerWalletID.'   '.'252';
                                
                            }
                       else{
                             wc_add_notice( 'Please enter the Phone Number for Billing (Format: 00252615080326 )', 'error' );
                                 return;
                       }
                   }
           
        }
        
        

    }

}

/**
 * Set up billing number for the payment gateway.
 *
 * @param array $description Fields added in the gateway platform.
 * @param int $payment_id    Order Payment ID.
 * @return void
 */
function walletpay_billing_phone_fields( $description, $payment_id ) {
    // echo $payment_id ;

    if ( 'wallet_pay' !== $payment_id ) {
        return $description;
    }

    ob_start();
    
    // Billing number Field.
    woocommerce_form_field(
        'walletpayerWalletID',
        array(
            'type' => 'text',
            'label' =>__( 'Enter Phone Number e.g 00252615080326', 'walletpay-pay-woo' ),
            'class' => array( 'form-row', 'form-row-wide', 'card-number' ),
            'required' => true,
        )
    );

    $description .= ob_get_clean();
    
    return $description;
}
				
function walletpay_billing_phone_save_field( $order_id ) {
    
    $order       = new WC_Order( $order_id );
    $order_total = intval( $order->get_total() );
    
    if ( $_POST['walletpayerWalletID'] ) {
        // echo $walletpayerWalletID .'    ' .'feil';
        update_post_meta( $order_id, 'walletpayerWalletID', esc_attr( $_POST['walletpayerWalletID'] ) );
    }

}
   
// function walletpay_billing_phone_show_field_admin_order( $order ) {    
//   $order_id = $order->get_id();
//   if ( get_post_meta( $order_id, 'walletpayerWalletID', true ) ) {
//       echo '<p><strong>Payment number:</strong> ' . get_post_meta( $order_id, 'walletpayerWalletID', true ) . '</p>';
  
//   }
// }