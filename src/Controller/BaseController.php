<?php

/**
 * Basic controller that is extended by this app controllers
 *
 * This controller has basic functionality that is shared between controllers
 *
 * PHP version 7.4
 *
 * @category   Controller
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Controller;

use JsonSchema\Validator;
use App\Service\Utilities;
use App\Service\BaseService;
use JsonSchema\Constraints\Constraint;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 *
 * Class BaseController is an extendible controller class from all the others.
 *
 * This class is mostly used in a generic way to provide widely useful, over the
 * controllers, functionality . It is the only controller class to extend the
 * AbstractController , with all the others extending this one , with the
 * implementation of the very core of all the controllers.
 *
 */
class BaseController extends AbstractController
{
    /**
     * Property $page points to the page is currently loaded and refers to
     * posts.
     *
     * @var Array
     */
    protected $page;

    /**
     * Property $headers contains the header of most responses.
     *
     * @var Array
     */
    private $headers;

    /**
     * Property $serializer points to jms serializer service.
     *
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Property $utilities points to methods provided in a custom service.
     *
     * @var Utilities
     */
    private $utilities;

    /**
     *
     * Function __construct() is the construct that sets properties with values.
     *
     * This constructor sets properties visible to all methods of this class with
     * injected services useful to all the other methods.
     *
     * @param SerializerInterface $serializer This argument injects a service
     * from jms.
     * @param Utilities $utilities This argument injects a custom created service.
     *
     */
    public function __construct(SerializerInterface $serializer, Utilities $utilities)
    {
        $this->serializer = $serializer;
        $this->headers = $this->getStandardHeaders();
        $this->utilities = $utilities;
    }

    /**
     * Function prepareResponse() gets an input of data that needs to returned as
     * as a response and after returns them as serialized json format.
     *
     * In this function the data input is an array or an object containing data
     * that will be returned as a json after adding information . The first thing
     * that will be added is the status of the user that will get the response .
     * The next thing that needs to be added is the type of the object if it is so.
     * Finally the number of the items that the response will contain.
     *
     * @param object|array $data object or array of objects with the data to respond
     * @param boolean $is_error if the data describe an error
     * @param array $groups the groups that this user belongs to
     * @param int $total the total number of results if paginated
     *
     * @return string $serialized the json response to be used by the response object
     *
     */
    protected function prepareResponse($data, $is_error = false)
    {
        if ($data === null) {
            return null;
        }
        if (!$is_error) {
            $data_type = $this->utilities->getClassName($data);
        } else {
            $data_type = 'error';
        }
        $transformed_data = [
            'type' => $data_type,
            'data' => $data,
        ];
        $serialized = $this->serializer->serialize($transformed_data, 'json');

        return $serialized;
    }

    /**
     * Function getHeaders() is a getter method for the headers that the responses
     * have.
     *
     * This function just returns the headers of the response. The return value
     * of this function is by default the one that sets the getStandardHeaders()
     * inside the constructor.
     *
     * @return array $headers This variable contains the header of a response.
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     *
     * Function getStandardHeaders() is a getter method that returns a default
     * value.
     *
     * In this function is just returned a default value to the only caller
     * function , the constructor of the class.
     *
     * @return array [] An array return setting the standard value of the header.
     *
     */
    private function getStandardHeaders()
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Function prepareMessageResponse() produces a json from an array in
     * order to be returned as a response.
     *
     * In this function what actually happens is an array recreation from
     * an input variable , which is finally get serialized to json format
     * and gets returned as a response.
     *
     * @param string $message the message to return
     * @param integer $status the status code to return
     *
     * @return string $serialized json to give to the response object
     *
     */
    protected function prepareMessageResponse($message, $type = 'message')
    {
        $response_array = [
            'type' => $type,
            'data' => $message,
        ];
        $serialized = $this->serializer->serialize($response_array, 'json');

        return $serialized;
    }

    /**
     * Function getHttpStatus() returns according to the service code input
     * the respective http status code.
     *
     * In this function what actually happens is a cross checking on the cases
     * stored in BaseService class and accordingly the appropriate http status
     * code is returned.
     *
     * @return int  The http response status code.
     */
    protected function getHttpStatus($code)
    {
        switch ($code) {
            case BaseService::VALIDATION_ERR:
                return Response::HTTP_BAD_REQUEST;
            case BaseService::CONFLICT_ERR:
                return Response::HTTP_CONFLICT;
            case BaseService::NOT_FOUND_ERR:
                return Response::HTTP_NOT_FOUND;
            case BaseService::SUCCESS:
                return Response::HTTP_OK;
            case BaseService::DELETED:
                return Response::HTTP_NO_CONTENT;
            case BaseService::FORBIDDEN_ERR:
                return Response::HTTP_FORBIDDEN;
            default:
                return Response::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    /**
     * Function validate() is used in order to validate data using json schema.
     *
     * This method gets a stdObject with the data to be validated, and some info
     * about the method that calls the validation, and returns an array
     * with validation errors, if they exist. If there are no errors, an empty
     * array is returned.
     * 
     * The bundle developed by justinrainbow supports multiple functionalities on
     * json schemas. Among all the others we will use two of those without taking
     * into consideration the default one that validates the properties of the provided
     * data against the given json schema. These functionalities , also named check modes
     * in the bundle, are referring to the type coercion and the application of default
     * values. The type coercion is something really useful because there are some cases
     * where values of variable are of different type of the one intended to use. The
     * application of default values is also really important as it helps to make code
     * more scalable.
     *
     * For more information about json schema you can check https://json-schema.org/.
     * The library used for this implementation is https://github.com/justinrainbow/json-schema.
     *
     * @param object $data a stdObject with the data to be validated
     * @param AbstractController $controller the object of the controller that calls the validation
     * @param string $method_name the name of the method that calls the validation
     *
     * @return array['code','data'] data: an array with the validation errors
     */
    protected function validate(object $data, AbstractController $controller, string $method_name, string $folder = ''): array
    {
        $schemas_dir = $this->getParameter('json_schemas_dir');
        $validator = new Validator();
        $dir = str_replace('App\Controller\\', '', get_class($controller));
        $dir = str_replace('\\', '/', $dir);
        $json_schema = $dir . '/' . $method_name . '.json';
        $validator->validate(
            $data,
            (object) ['$ref' => 'file://' . $schemas_dir . '/' . $json_schema],
            Constraint::CHECK_MODE_COERCE_TYPES
        );
        $validator->validate(
            $data,
            (object) ['$ref' => 'file://' . $schemas_dir . '/' . $json_schema],
            Constraint::CHECK_MODE_APPLY_DEFAULTS
        );
        $errors = $validator->getErrors();
        if (count($errors) == 0) {
            return [
                'code' => BaseService::SUCCESS,
                'data' => [],
            ];
        }
        /**
         * Creates a simplified array with the validation errors.
         */
        $formated_errors = [];
        foreach ($errors as $error) {
            $formated_errors[] = [
                'property_path' => $error['property'],
                'message' => $error['message'],
                'constraint' => $error['constraint'],
            ];
        }

        return [
            'code' => BaseService::VALIDATION_ERR,
            'data' => $formated_errors,
        ];
    }

    /**
     * Function respond() creates a Response object using the provided data
     *
     * Gets the result from a service or validation and creates a response
     * with everything needed.
     *
     * @param array $result['code','data'] the result from a service
     * @param array $groups the groups that the user belongs to
     *
     * @return Response
     */
    protected function respond(array $result, array $groups = []): Response
    {
        $success_codes = [BaseService::SUCCESS, BaseService::DELETED];
        $total = null;
        $is_error = true;
        if (isset($result['total'])) {
            $total = $result['total'];
        }
        if (in_array($result['code'], $success_codes)) {
            $is_error = false;
        }
        $status = $this->getHttpStatus($result['code']);
        $data = $this->prepareResponse($result['data'], $is_error, $groups, $total);
        $headers = $this->getHeaders();

        return new Response($data, $status, $headers);
    }

    /**
     * Function respondNotFound() creates a basic not found response object
     *
     * This method creates a basic response for not found cases. The message
     * is optional, so if not given, a default message will be used.
     *
     * @param string $message message for the response body
     *
     * @return Response a response object
     */
    protected function respondNotFound(string $message = "Resource not found."): Response
    {
        $status = Response::HTTP_NOT_FOUND;
        $body = [
            'message' => $message,
        ];
        $data = $this->prepareResponse($body, 1);
        $headers = $this->getHeaders();

        return new Response($data, $status, $headers);
    }
}
