<?php

/**
 * The NfyQueue class acts like the CLogger class. Instead of collecting the messages,
 * it instantly processes them, similar to CLogRouter calling collectLogs on each route on log flush event.
 */
abstract class NfyQueue extends CApplicationComponent implements NfyQueueInterface
{
	/**
	 * @var string $name Human readable name of the queue, required.
	 */
	public $name;
	/**
	 * @var integer $timeout Number of seconds after which a locked message is considered timed out and available again.
	 * If null, locked messages never time out.
	 */
	public $timeout;

	/**
	 * @inheritdoc
	 */
    public function beforeSend($message)
	{
		if($this->hasEventHandler('onBeforeSend'))
		{
			$event=new CModelEvent($this, array('message'=>$message));
			$this->onBeforeSend($event);
			return $event->isValid;
		}
		else
			return true;
	}
	/**
	 * @inheritdoc
	 */
    public function afterSend($message)
	{
		$this->onAfterSend(new CEvent($this, array('message'=>$message)));
	}
	/**
	 * @inheritdoc
	 */
    public function beforeSendSubscription($message, $subscriber_id)
	{
		if($this->hasEventHandler('onBeforeSendSubscription'))
		{
			$event=new CModelEvent($this, array('message'=>$message, 'subscriber_id'=>$subscriber_id));
			$this->onBeforeSendSubscription($event);
			return $event->isValid;
		}
		else
			return true;
	}
	/**
	 * @inheritdoc
	 */
    public function afterSendSubscription($message, $subscriber_id)
	{
		$this->onAfterSendSubscription(new CEvent($this, array('message'=>$message, 'subscriber_id'=>$subscriber_id)));
	}
	/**
	 * This event is raised before the message is sent to the queue.
	 * @param CModelEvent $event the event parameter
	 */
    public function onBeforeSend($event)
	{
		$this->raiseEvent('onBeforeSend',$event);
	}
	/**
	 * This event is raised after the message is sent to the queue.
	 * @param CEvent $event the event parameter
	 */
    public function onAfterSend($event)
	{
		$this->raiseEvent('onAfterSend',$event);
	}
	/**
	 * This event is raised before the message is sent to a subscription.
	 * @param CModelEvent $event the event parameter
	 */
    public function onBeforeSendSubscription($event)
	{
		$this->raiseEvent('onBeforeSendSubscription',$event);
	}
	/**
	 * This event is raised after the message is sent to a subscription.
	 * @param CEvent $event the event parameter
	 */
    public function onAfterSendSubscription($event)
	{
		$this->raiseEvent('onAfterSendSubscription',$event);
	}
}