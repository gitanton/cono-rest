<?php
/**
 * Webhooks for stripe to notify us that a user's card is bad or expired or something failed.
 * Class Stripe
 */

class Stripe extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
    }

    public function index() {
        $postdata = file_get_contents("php://input");
        if($postdata) {
            $event = json_decode($postdata);
            if ($event) {
                $text = sprintf('[Stripe Event] Processing [%s] Hook', $event->type);

                log_message('info', $text);

                loggly(array(
                    'text' => $text,
                    'method' => 'stripe.index',
                    'event' => $event
                ));

                switch ($event->type) {
                    case "charge.failed":
                        $this->charge_failed($event);
                        break;
                    case "charge.succeeded":
                        $this->charge_succeeded($event);
                        break;
                }

                log_message('info', sprintf('[Stripe Event] Event [%s] Details: [%s] ', $event->type, print_r($event, TRUE)));
            }
        }  else {
            echo "NO INPUT";
        }
    }

    /*
     * Send an email to a user when their charge has failed
     */
    private function charge_failed($event) {
        $this->load->model('Subscription');
        $this->load->helper('notification');
        $customer_id = $event->data->object->customer;
        $card_last_four = $event->data->object->card->last4;

        $subscription = $this->Subscription->load_by_field('stripe_customer_id', $customer_id);
        if($subscription) {
            /* Their subscription has failed to charge */
            $this->Subscription->update($subscription->id, array(
                'failed' => 1,
                'failed_event' => json_encode($event),
                'updated' => timestamp_to_mysqldatetime(now())
            ));
            $text = sprintf('[Stripe Event] charge.failed for subscription [%d]', $subscription->id);
            loggly(array(
                'text' => $text,
                'method' => 'stripe.charge_failed',
                'event' => $event
            ));
            $user = $this->User->load($subscription->user_id);
            notify_failed_charge($user, $card_last_four);
        }
    }

    /*
     * Send an email to a user when their charge has failed
     */
    private function charge_succeeded($event) {
        $this->load->model('Subscription');
        $this->load->helper('notification');
        $customer_id = $event->data->object->customer;
        $card_last_four = $event->data->object->card->last4;
        $amount = round($event->data->object->amount / 100,2);

        $subscription = $this->Subscription->load_by_field('stripe_customer_id', $customer_id);
        if($subscription) {
            /* Their subscription has failed to charge */
            $this->Subscription->update($subscription->id, array(
                'updated' => timestamp_to_mysqldatetime(now())
            ));
            $text = sprintf('[Stripe Event] charge.succeeded for subscription [%d]', $subscription->id);
            loggly(array(
                'text' => $text,
                'method' => 'stripe.charge_succeeded',
                'event' => $event
            ));
            $user = $this->User->load($subscription->user_id);
            notify_successful_charge($user, $card_last_four, $amount);
        }
    }
}

?>