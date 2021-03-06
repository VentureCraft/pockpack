<?php namespace Duellsy\Pockpack;

use Guzzle\Http\Client;
use Duellsy\Pockpack\EmptyConstructorException;
use Duellsy\Pockpack\NoPockpackQueueException;

/**
 * The Pockpack package is a quick wrap to make connecting and
 * consuming the pocket API much simpler and quicker to get up and running.
 * For information / documentation on using this package, please refer to:
 * https://github.com/duellsy/pockpack
 *
 * @package    Pockpack
 * @version    2.0.0
 * @author     Chris Duell
 * @license    MIT
 * @copyright  (c) 2013 Chris Duell
 * @link       https://github.com/duellsy/pockpack
 */
class Pockpack
{

    private $consumer_key = '';
    private $access_token = '';

    const BASE_URL = 'https://getpocket.com';

    public function __construct($consumer_key = null, $access_token = null)
    {

        if( is_null($consumer_key) OR is_null($access_token) OR $consumer_key == '' OR $access_token == '') {
            throw new EmptyConstructorException("consumer_key and access_token are required params when initiating Pockpack");
        }

        $this->consumer_key = $consumer_key;
        $this->access_token = $access_token;

    }


    /**
     * Responsible for sending the request to the pocket API
     *
     * @param  string $consumer_key
     * @param  string $access_token
     * @param  array $actions
     */
    public function send(PockpackQueue $queue = null)
    {

        if( is_null($queue) ) {
            throw new NoPockpackQueueException();
        }

        $actions = json_encode($queue->getActions());
        $actions = urlencode($actions);

        $client = new Client(self::BASE_URL);
        $request = $client->get(
            '/v3/send?actions=' . $actions .
            '&consumer_key=' . $this->consumer_key .
            '&access_token=' . $this->access_token
        );

        $response = $request->send();

        // remove any items from the queue
        $queue->clear();

        return json_decode($response->getBody());

    }




    /**
     * Get a list of active bookmarks from the API
     *
     * @param  string $consumer_key
     * @param  string $access_token
     */
    public function retrieve($options = array())
    {

        $params = array(
            'consumer_key'  => $this->consumer_key,
            'access_token'  => $this->access_token
        );

        // combine the creds with any options sent
        $params = array_merge($params, $options);

        $client = new Client(self::BASE_URL);
        $request = $client->post('/v3/get');
        $request->getParams()->set('redirect.strict', true);
        $request->setHeader('Content-Type', 'application/json; charset=UTF8');
        $request->setHeader('X-Accept', 'application/json');
        $request->setBody(json_encode($params));
        $response = $request->send();

        return json_decode($response->getBody());

    }

}
