# SfynxRestClientBundle Configuration Reference

The following documents are available:

- [Declare an API](#declare-an-API)
- [Define and use a cache provider (optional)](dDefine-and-use-a-cache-provider-(optional))
- [Define and use circuit breaker provider (optional)](#define-and-use-circuit-breaker-provider-(optional))
- [Setup the development environment (optional)](#detup-the-development-environment-(optional))
- [A full configuration example](#a-full-configuration-example)

## Declare an API

``` yaml
# app/config/config.yml

sfynx_rest_client:
    http_cacher: ~
    log_enabled: true
    api:
        my_api_name:             # [Required] Api name, used to identify it in the container: 'sfynx_rest_client.api.my_api_name'
            endpoint_root:  ~    # [Required] The base url of your API (from which all path will be related to).
            security_token: ~    # [Required] An API token to authenticate your client in your API.
            circuit_breaker: ~   # [Optional] The circuit breaker name value
            cache_enabled:  true # [Optional] Use the cache feature if you activated it and the response of your API says it can be set in cache.
            # [Optional part]
            client:
                service:     sfynx_rest_client.api             # [Optional] The API client service. Define your own to provide an easy and sharable interface to the API.
                implementor: sfynx_rest_client.api_implementor # [Optional] The API client implementor service. Define your own if you want to change the behaviour of the communication with the API.

        # You can define as many api as you want.
        # my_api_name2:
        #     ...
```

## Define and use a cache provider (optional)

First define a provider using doctrine_cache:
```yaml
# app/config/config.yml

doctrine_cache:
    providers:
        sfynx_rest_client:
            predis:
                host: sfynx_redis
                port: 6379
                database: 11
```

Take a look at [DoctrineCacheBundle](https://github.com/doctrine/DoctrineCacheBundle) for more informations.

Then reference it:
```yaml
sfynx_rest_client:
    http_cacher: doctrine_cache.providers.sfynx_rest_client
```

## Define and use circuit breaker provider (optional)

First define a provider using doctrine_cache:
```yaml
# app/config/config.yml

sfynx_circuit_breaker:
    cache_dir: '/tmp/'
    service_names:
        cb_my_api:
            max_failure: 5
            reset_time: 30
```

Take a look at [SfynxCircuitBreakerBundle](https://github.com/pigroupe/SfynxCircuitBreakerBundle/blob/master/Resources/doc/index.md) for more informations.

Then reference it:
```yaml
sfynx_rest_client:
    api:
        my_api:
            circuit_breaker: 'cb_my_api'
```

## Setup the development environment (optional)

In order to profile Api logs, enabled this feature:
```yaml
# app/config/config_dev.yml

sfynx_rest_client:
    log_enabled: true
```

## A full configuration example

```yaml
sfynx_circuit_breaker:
    cache_dir: '/tmp/'
    service_names:
        cb_my_api:
            max_failure: 5
            reset_time: 30
        cb_my_api2:
            max_failure: 15
            reset_time: 90

doctrine_cache:
    providers:
        sfynx_rest_client:
            predis:
                host: sfynx_redis
                port: 6379
                database: 11

sfynx_rest_client:
    log_enabled: false
    http_cacher: doctrine_cache.providers.sfynx_rest_client
    api:
        my_api:
            endpoint_root:  %api.my_api.endpoint_root%
            security_token: %api.my_api.security_token%
            circuit_breaker: 'cb_my_api'
            cache_enabled:  true
            client:
                service: my_bundle.api.my_api
        my_api2:
            endpoint_root:  %api.my_api2.endpoint_root%
            security_token: %api.my_api2.security_token%
            circuit_breaker: 'cb_my_api2'
            cache_enabled:  true
```
