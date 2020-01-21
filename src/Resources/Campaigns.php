<?php

namespace Oktave\Resources;

use DateTime;
use GuzzleHttp\Exception\GuzzleException;
use Oktave\Exceptions\AuthenticationException;
use Oktave\Exceptions\InvalidArgumentException;
use Oktave\Exceptions\InvalidContentType;
use Oktave\Exceptions\InvalidRequestMethod;
use Oktave\Resource as Resource;
use Oktave\Response;

class Campaigns extends Resource
{
    /**
     * {@inheritDoc}
     */
    public $resourceCollection = 'emitters';

    /**
     * {@inheritDoc}
     */
    public $resource = 'emitter';

    /**
     *  Send a specific campaign
     *
     * @param  string  $id
     * @param          $recipients
     * @param  null    $scheduleDate
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws GuzzleException
     * @throws AuthenticationException
     * @throws InvalidContentType
     * @throws InvalidRequestMethod
     */
    public function send(string $id, $recipients, $scheduleDate = null): Response
    {
        $recipients = $this->prepareRecipients($recipients);

        $data = [
            'recipients' => $recipients,
            'emitter' => [
                'scheduled_for' => null,
                'delay' => 0,
            ]
        ];

        if ($scheduleDate instanceof DateTime) {
            $data['emitter']['scheduled_for'] = $scheduleDate->format(DateTime::ISO8601);
        }

        if (is_integer($scheduleDate)) {
            $data['emitter']['delay'] = $scheduleDate;
        }

        return $this->call('put', $data, $id.'/send');
    }

    /**
     * Reformat the recipients source for API call
     *
     * @param  array|string  $recipients
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function prepareRecipients($recipients)
    {
        if (is_string($recipients)) {
            return [$recipients];
        }

        if (is_array($recipients)) {
            if (count($recipients) === 0) {
                throw new InvalidArgumentException('Recipients list cannot be empty');
            }
            // ensure that $recipients is an array of recipient data
            if ($this->isRecipientDataArray($recipients)) {
                return [$recipients];
            }
            // ensure that $recipients is an array of recipients and not an array of recipient data array
            /*if (!$this->isRecipientDataArray($recipients[0])) {
                return $recipients;
            }*/
        }

        return $recipients;
    }

    /**
     * Check if the provided recipient is a recipient data array (associative array, string key/value pairs)
     *
     * @param  mixed  $recipient
     *
     * @return bool
     */
    private function isRecipientDataArray($recipient)
    {
        return is_array($recipient) && array_keys($recipient) !== range(0, count($recipient) - 1);
    }
}
