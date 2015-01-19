<?php

//Yii::import('NfyDbSubscription');
//Yii::import('NfyDbSubscriptionCategory');

/**
 * SubscribeBehavior class file.
 *
 * @author Patryk Radziszewski<pradziszewski@nets.com.pl>
 */
class SubscribeBehavior extends CModelBehavior
{

    /**
     * subscribe user to category in notification queue
     *
     * @param int $subscriber_id
     * @param string $queueId
     * @param type $label
     * @param type $categories
     * @param type $exceptions
     * @return boolean
     * @throws CException
     */
    public function subscribeUser($subscriber_id, $queueId, $label = null, $categories = null, $exceptions = null)
    {
        $trx = NfyDbSubscription::model()->getDbConnection()->getCurrentTransaction() !== null ? null : NfyDbSubscription::model()->getDbConnection()->beginTransaction();
        $subscription = NfyDbSubscription::model()->withQueue($queueId)->withSubscriber($subscriber_id)->find();

        if ($subscription === null) {
            $subscription = new NfyDbSubscription;
            $subscription->setAttributes(array(
                'queue_id' => $queueId,
                'subscriber_id' => $subscriber_id,
                'label' => $label,
            ));
            if (!$subscription->save())
                throw new CException(Yii::t('NfyModule.app', 'Failed to subscribe {subscriber_id} to {queue_label}', array('{subscriber_id}' => $subscriber_id, '{queue_label}' => $label)));
        } else if ($subscription->is_deleted) {
            $subscription->is_deleted = false;
        }
        $this->saveSubscriptionCategories($categories, $subscription->primaryKey, false);
        $this->saveSubscriptionCategories($exceptions, $subscription->primaryKey, true);
        if ($trx !== null) {
            $trx->commit();
        }
        return true;
    }

    protected function saveSubscriptionCategories($categories, $subscription_id, $are_exceptions = false)
    {
        if ($categories === null)
            return true;
        if (!is_array($categories))
            $categories = array($categories);
        foreach ($categories as $category) {
            try {
                $subscriptionCategory = new NfyDbSubscriptionCategory;
                $subscriptionCategory->setAttributes(array(
                    'subscription_id' => $subscription_id,
                    'category' => str_replace('*', '%', $category),
                    'is_exception' => $are_exceptions ? 1 : 0,
                ));

                if (!$subscriptionCategory->save()) {
                    throw new CException(Yii::t('NfyModule.app', 'Failed to save category {category} for subscription {subscription_id}', array('{category}' => $category, '{subscription_id}' => $subscription_id)));
                }
            } catch (CDbException $ex) {
                // this is probably due to constraint violation, ignore
                // TODO: distinct from constraint violation and other database exceptions
            }
        }
        return true;
    }

}
