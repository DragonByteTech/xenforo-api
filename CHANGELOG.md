CHANGELOG
=========

Unreleased
----------

* A 4xx is logged at `info`, not `error`: XenForo answered, and the typed exception
  carries the answer. `error` is for a forum that did not - a transport failure, a 5xx,
  a 2xx that is not JSON
* The query string is left out of every logged URI, since on a lookup endpoint it is
  the lookup

1.2.0 (2026-09-13)
------------------

* `Client::withKey($baseUri, $key, $client)` - the short form for an API key, so the
  ordinary case no longer names a `Config` and an `ApiKey` to accept both defaults
* README states which XenForo versions answer which endpoints - 154 of the 162 on 2.3,
  144 on 2.2, 124 on 2.1 - measured on a real forum of each
* The `write` exercise no longer stops at `threads/{id}/mark-read` on a forum older
  than 2.2

1.1.0 (2026-09-09)
------------------

* The PSR-17 factories are optional on `Client`: when not given, Guzzle's, Nyholm's or
  Diactoros' is found automatically
* Every generated entity, `SiteInfo`, `SiteStats`, `Embed` and `ApiResponse` implement
  `\JsonSerializable`, serialising to the payload as it arrived

1.0.0 (2026-09-09)
------------------

* Initial release
* `src/Generated/Schema` generated from `resources/openapi.json`, taken from
  https://github.com/xenforo-ltd/docs (`static/api/openapi.json` at commit `1f43b195`,
  2026-03-18)
