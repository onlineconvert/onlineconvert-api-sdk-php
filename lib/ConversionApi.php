<?php
/**
 *  Copyright 2015 SmartBear Software
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/**
 *
 * NOTE: This class is auto generated by the swagger code generator program. Do not edit the class manually.
 */

namespace Qaamgo;

class ConversionApi {

    private $apiClient; // instance of the ApiClient

    function __construct(ApiClient $apiClient) {
        $this->apiClient = $apiClient;
  }

  /**
   * get the API client
   */
  public function getApiClient() {
    return $this->apiClient;
  }

  /**
   * set the API client
   */
  public function setApiClient($apiClient) {
    $this->apiClient = $apiClient;
  }


  /**
   * jobsJobIdConversionsGet
   *
   * Get list of conversions defined for the current job.
   *
   * @param string $x_oc_token Token for authentication for the current job (required)
   * @param string $x_oc_api_key Api key for the user to filter. (required)
   * @param string $job_id ID of job that needs to be fetched (required)
   * @return array[Conversion]
   */
   public function jobsJobIdConversionsGet($x_oc_token, $x_oc_api_key, $job_id) {

      // verify the required parameter 'job_id' is set
      if ($job_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $job_id when calling jobsJobIdConversionsGet');
      }


      // parse inputs
      $resourcePath = "/jobs/{job_id}/conversions";
      $resourcePath = str_replace("{format}", "json", $resourcePath);
      $method = "GET";
      $httpBody = '';
      $queryParams = array();
      $headerParams = array();
      $formParams = array();
      $_header_accept = $this->apiClient->selectHeaderAccept(array());
      if (!is_null($_header_accept)) {
        $headerParams['Accept'] = $_header_accept;
      }
      $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());


      // header params
      if($x_oc_token !== null) {
        $headerParams['X-Oc-Token'] = $this->apiClient->toHeaderValue($x_oc_token);
      }// header params
      if($x_oc_api_key !== null) {
        $headerParams['X-Oc-Api-Key'] = $this->apiClient->toHeaderValue($x_oc_api_key);
      }
      // path params
      if($job_id !== null) {
        $resourcePath = str_replace("{" . "job_id" . "}",
                                    $this->apiClient->toPathValue($job_id), $resourcePath);
      }



      // for model (json/xml)
      if (isset($_tempBody)) {
        $httpBody = $_tempBody; // $_tempBody is the method argument, if present
      } else if (count($formParams) > 0) {
        // for HTTP post (form)
        $httpBody = $formParams;
      }

      // authentication setting, if any
      $authSettings = array();

      // make the API Call
      $response = $this->apiClient->callAPI($resourcePath, $method,
                                            $queryParams, $httpBody,
                                            $headerParams, $authSettings);

      if(! $response) {
        return null;
      }

      $responseObject = $this->apiClient->deserialize($response,'array[Conversion]');
      return $responseObject;
  }

  /**
   * jobsJobIdConversionsPost
   *
   * Adds a new conversion to the given job.
   *
   * @param Conversion $body information for the conversion. (required)
   * @param string $x_oc_token Token for authentication for the current job (required)
   * @param string $x_oc_api_key Api key for the user to filter. (required)
   * @param string $job_id ID of job that needs to be fetched (required)
   * @return Conversion
   */
   public function jobsJobIdConversionsPost($body, $x_oc_token, $x_oc_api_key, $job_id) {

      // verify the required parameter 'body' is set
      if ($body === null) {
        throw new \InvalidArgumentException('Missing the required parameter $body when calling jobsJobIdConversionsPost');
      }

      // verify the required parameter 'job_id' is set
      if ($job_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $job_id when calling jobsJobIdConversionsPost');
      }


      // parse inputs
      $resourcePath = "/jobs/{job_id}/conversions";
      $resourcePath = str_replace("{format}", "json", $resourcePath);
      $method = "POST";
      $httpBody = '';
      $queryParams = array();
      $headerParams = array();
      $formParams = array();
      $_header_accept = $this->apiClient->selectHeaderAccept(array());
      if (!is_null($_header_accept)) {
        $headerParams['Accept'] = $_header_accept;
      }
      $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());


      // header params
      if($x_oc_token !== null) {
        $headerParams['X-Oc-Token'] = $this->apiClient->toHeaderValue($x_oc_token);
      }// header params
      if($x_oc_api_key !== null) {
        $headerParams['X-Oc-Api-Key'] = $this->apiClient->toHeaderValue($x_oc_api_key);
      }
      // path params
      if($job_id !== null) {
        $resourcePath = str_replace("{" . "job_id" . "}",
                                    $this->apiClient->toPathValue($job_id), $resourcePath);
      }

      // body params
      $_tempBody = null;
      if (isset($body)) {
        $_tempBody = $body;
      }

      // for model (json/xml)
      if (isset($_tempBody)) {
        $httpBody = $_tempBody; // $_tempBody is the method argument, if present
      } else if (count($formParams) > 0) {
        // for HTTP post (form)
        $httpBody = $formParams;
      }

      // authentication setting, if any
      $authSettings = array();

      // make the API Call
      $response = $this->apiClient->callAPI($resourcePath, $method,
                                            $queryParams, $httpBody,
                                            $headerParams, $authSettings);

      if(! $response) {
        return null;
      }

      $responseObject = $this->apiClient->deserialize($response,'Conversion');
      return $responseObject;
  }

  /**
   * jobsJobIdConversionsConversionIdGet
   *
   * Get list of conversions defined for the current job.
   *
   * @param string $x_oc_token Token for authentication for the current job (required)
   * @param string $x_oc_api_key Api key for the user to filter. (required)
   * @param string $job_id ID of job that needs to be fetched (required)
   * @param string $conversion_id Identifier for the job conversion. (required)
   * @return Conversion
   */
   public function jobsJobIdConversionsConversionIdGet($x_oc_token, $x_oc_api_key, $job_id, $conversion_id) {

      // verify the required parameter 'job_id' is set
      if ($job_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $job_id when calling jobsJobIdConversionsConversionIdGet');
      }

      // verify the required parameter 'conversion_id' is set
      if ($conversion_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $conversion_id when calling jobsJobIdConversionsConversionIdGet');
      }


      // parse inputs
      $resourcePath = "/jobs/{job_id}/conversions/{conversion_id}";
      $resourcePath = str_replace("{format}", "json", $resourcePath);
      $method = "GET";
      $httpBody = '';
      $queryParams = array();
      $headerParams = array();
      $formParams = array();
      $_header_accept = $this->apiClient->selectHeaderAccept(array());
      if (!is_null($_header_accept)) {
        $headerParams['Accept'] = $_header_accept;
      }
      $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());


      // header params
      if($x_oc_token !== null) {
        $headerParams['X-Oc-Token'] = $this->apiClient->toHeaderValue($x_oc_token);
      }// header params
      if($x_oc_api_key !== null) {
        $headerParams['X-Oc-Api-Key'] = $this->apiClient->toHeaderValue($x_oc_api_key);
      }
      // path params
      if($job_id !== null) {
        $resourcePath = str_replace("{" . "job_id" . "}",
                                    $this->apiClient->toPathValue($job_id), $resourcePath);
      }// path params
      if($conversion_id !== null) {
        $resourcePath = str_replace("{" . "conversion_id" . "}",
                                    $this->apiClient->toPathValue($conversion_id), $resourcePath);
      }



      // for model (json/xml)
      if (isset($_tempBody)) {
        $httpBody = $_tempBody; // $_tempBody is the method argument, if present
      } else if (count($formParams) > 0) {
        // for HTTP post (form)
        $httpBody = $formParams;
      }

      // authentication setting, if any
      $authSettings = array();

      // make the API Call
      $response = $this->apiClient->callAPI($resourcePath, $method,
                                            $queryParams, $httpBody,
                                            $headerParams, $authSettings);

      if(! $response) {
        return null;
      }

      $responseObject = $this->apiClient->deserialize($response,'Conversion');
      return $responseObject;
  }

  /**
   * jobsJobIdConversionsConversionIdDelete
   *
   * Removes the conversion for a job.
   *
   * @param string $x_oc_token Token for authentication for the current job (required)
   * @param string $x_oc_api_key Api key for the user to filter. (required)
   * @param string $job_id ID of job that needs to be fetched (required)
   * @param string $conversion_id Identifier for the job conversion. (required)
   * @return Conversion
   */
   public function jobsJobIdConversionsConversionIdDelete($x_oc_token, $x_oc_api_key, $job_id, $conversion_id) {

      // verify the required parameter 'job_id' is set
      if ($job_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $job_id when calling jobsJobIdConversionsConversionIdDelete');
      }

      // verify the required parameter 'conversion_id' is set
      if ($conversion_id === null) {
        throw new \InvalidArgumentException('Missing the required parameter $conversion_id when calling jobsJobIdConversionsConversionIdDelete');
      }


      // parse inputs
      $resourcePath = "/jobs/{job_id}/conversions/{conversion_id}";
      $resourcePath = str_replace("{format}", "json", $resourcePath);
      $method = "DELETE";
      $httpBody = '';
      $queryParams = array();
      $headerParams = array();
      $formParams = array();
      $_header_accept = $this->apiClient->selectHeaderAccept(array());
      if (!is_null($_header_accept)) {
        $headerParams['Accept'] = $_header_accept;
      }
      $headerParams['Content-Type'] = $this->apiClient->selectHeaderContentType(array());


      // header params
      if($x_oc_token !== null) {
        $headerParams['X-Oc-Token'] = $this->apiClient->toHeaderValue($x_oc_token);
      }// header params
      if($x_oc_api_key !== null) {
        $headerParams['X-Oc-Api-Key'] = $this->apiClient->toHeaderValue($x_oc_api_key);
      }
      // path params
      if($job_id !== null) {
        $resourcePath = str_replace("{" . "job_id" . "}",
                                    $this->apiClient->toPathValue($job_id), $resourcePath);
      }// path params
      if($conversion_id !== null) {
        $resourcePath = str_replace("{" . "conversion_id" . "}",
                                    $this->apiClient->toPathValue($conversion_id), $resourcePath);
      }



      // for model (json/xml)
      if (isset($_tempBody)) {
        $httpBody = $_tempBody; // $_tempBody is the method argument, if present
      } else if (count($formParams) > 0) {
        // for HTTP post (form)
        $httpBody = $formParams;
      }

      // authentication setting, if any
      $authSettings = array();

      // make the API Call
      $response = $this->apiClient->callAPI($resourcePath, $method,
                                            $queryParams, $httpBody,
                                            $headerParams, $authSettings);

      if(! $response) {
        return null;
      }

      $responseObject = $this->apiClient->deserialize($response,'Conversion');
      return $responseObject;
  }



}
