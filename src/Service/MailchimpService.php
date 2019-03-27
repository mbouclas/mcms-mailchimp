<?php

namespace Mcms\Mailchimp\Service;
use Carbon\Carbon;
use DrewM\MailChimp\MailChimp;
use GeoIP;
use GeoIp2\Exception\AddressNotFoundException;
use Mcms\Mailchimp\Exceptions\InvalidMaichimpKey;
use Mcms\Mailchimp\Exceptions\InvalidMailchimpList;

class MailchimpService
{
    /** @var \DrewM\MailChimp\MailChimp */
    public $mailChimp;

    /** * @var MailchimpListCollection */
    protected $lists;

    /**

     * @param MailchimpListCollection $lists
     */
    public function __construct(MailchimpListCollection $lists = null)
    {
        $key = env('MAILCHIMP_APIKEY');
        if ( ! $key) {
            throw new InvalidMaichimpKey('Mailchimp key not set');
        }

        $this->mailChimp = new MailChimp($key);

        $this->lists = $lists;
    }

    /**
     * @param string $email
     * @param array  $mergeFields
     * @param string $listName
     * @param array  $options
     *
     * @return array|bool
     *
     * @throws InvalidMailchimpList
     */
    public function subscribe($email, $mergeFields = [], $listName = '', $options = [])
    {
        $list = $this->lists->findByName($listName);
        $mergeFieldsFound = $this->getMergeFields($list->getId());
        $mergeFieldsAllowed = $mergeFieldsFound->pluck('tag')->toArray();
        $nameFields = [
            'fullName',
            'full_name',
            'name'
        ];

        $defaultOptions = [
            'email_address' => $email,
            'status' => 'subscribed',
            'email_type' => 'html',
            'timestamp_signup' => Carbon::now()->format('Y-m-d\TH:i:sP'),
            'ip_signup' => \Request::ip(),
        ];


        if (count($mergeFields) > 0) {
            foreach ($mergeFields as $key => $value){
                if (in_array($key, $mergeFieldsAllowed)){
                    $defaultOptions['merge_fields'][$key] = $value;
                }

                if (in_array($key, $nameFields)){
                    $tmp = explode(' ', $value);
                    $defaultOptions['merge_fields']['FNAME'] = $tmp[0];
                    $defaultOptions['merge_fields']['LNAME'] = (isset($tmp[1])) ? $tmp[1] : '';
                }
            }

        }


        try {
            $location = geoip()->getLocation( $defaultOptions['ip_signup'] );
            $defaultOptions['location'] = [
                'latitude' => $location->lat,
                'longitude' => $location->lon,
                'country_code' => $location->iso_code,
                'timezone' => $location->timezone,
            ];
        }
        catch(AddressNotFoundException $e){

        }

        $options = array_merge($defaultOptions, $options);

        $response = $this->mailChimp->post("lists/{$list->getId()}/members", $options);

        if (! $this->lastActionSucceeded()) {
            return false;
        }

        return $response;
    }

    public function editMember($email, $mergeFields = [], $listName = '', $options = [])
    {
        $list = $this->lists->findByName($listName);
        $mergeFieldsFound = $this->getMergeFields($list->getId());
        $mergeFieldsAllowed = $mergeFieldsFound->pluck('tag')->toArray();
        $nameFields = [
            'fullName',
            'full_name',
            'name'
        ];

        $defaultOptions = [
            'email_type' => 'html',
        ];

        $options = array_merge($defaultOptions, $options);

        $response = $this->mailChimp->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", $options);

        if (! $this->lastActionSucceeded()) {
            return false;
        }

        return $response;
    }

    /**
     * @param string $email
     * @param string $listName
     *
     * @return array|bool
     *
     * @throws InvalidMailchimpList
     */
    public function getMember($email, $listName = '')
    {
        $list = $this->lists->findByName($listName);

        return $this->mailChimp->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
    }

    public function getMergeFields($listId)
    {

        $response = $this->mailChimp->get("lists/{$listId}/merge-fields");
        if (! $this->lastActionSucceeded()) {
            return false;
        }

        return $this->lists->mergeFields($response);
    }

    /**
     * @param string $email
     * @param string $listName
     *
     * @return bool
     */
    public function hasMember($email, $listName = '')
    {
        $response = $this->getMember($email, $listName);

        if (! isset($response['email_address'])) {
            return false;
        }

        if (strtolower($response['email_address']) != strtolower($email)) {
            return false;
        }

        return true;
    }

    /**
     * @param $email
     * @param string $listName
     *
     * @return array|false
     *
     * @throws InvalidMailchimpList
     */
    public function unsubscribe($email, $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->mailChimp->patch("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}", [
            'status' => 'unsubscribed',
        ]);

        return $response;
    }

    /**
     * @param $email
     * @param string $listName
     *
     * @return array|false
     *
     * @throws InvalidMailchimpList
     */
    public function delete($email, $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->mailChimp->delete("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");

        return $response;
    }

    /**
     * @param string $fromName
     * @param string $replyTo
     * @param string $subject
     * @param string $html
     * @param string $listName
     * @param array  $options
     * @param array  $contentOptions
     *
     * @return array|bool
     *
     * @throws InvalidMailchimpList
     */
    public function createCampaign($fromName, $replyTo, $subject, $html = '', $listName = '', $options = [], $contentOptions = [])
    {
        $list = $this->lists->findByName($listName);

        $defaultOptions = [
            'type' => 'regular',
            'recipients' => [
                'list_id' => $list->getId(),
            ],
            'settings' => [
                'subject_line' => $subject,
                'from_name' => $fromName,
                'reply_to' => $replyTo,
            ],
        ];

        $options = array_merge($defaultOptions, $options);

        $response = $this->mailChimp->post('campaigns', $options);

        if (! $this->lastActionSucceeded()) {
            return false;
        }

        if ($html === '') {
            return $response;
        }

        if (! $this->updateContent($response['id'], $html, $contentOptions)) {
            return false;
        }

        return $response;
    }

    public function updateContent($campaignId, $html, $options = [])
    {
        $defaultOptions = compact('html');

        $options = array_merge($defaultOptions, $options);

        $response = $this->mailChimp->put("campaigns/{$campaignId}/content", $options);

        if (! $this->lastActionSucceeded()) {
            return false;
        }

        return $response;
    }

    /**
     * @return \DrewM\MailChimp\MailChimp
     */
    public function getApi()
    {
        return $this->mailChimp;
    }

    /**
     * @return array|false
     */
    public function getLastError()
    {
        return $this->mailChimp->getLastError();
    }

    /**
     * @return bool
     */
    public function lastActionSucceeded()
    {
        return ! $this->mailChimp->getLastError();
    }

    /**
     * @param string $email
     *
     * @return string
     */
    protected function getSubscriberHash($email)
    {
        return $this->mailChimp->subscriberHash($email);
    }
}