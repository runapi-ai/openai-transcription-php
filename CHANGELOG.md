# Changelog

## [v0.2.0](https://github.com/runapi-ai/openai-transcription-php/releases/tag/v0.2.0) - 2026-09-30

### Changed
- Send request parameters to the service without local validation. Model ids and parameter values the service supports work without an SDK upgrade; static types and enum constants remain for completion.
  Migration: Invalid parameters now throw `ValidationException` built from the service's 400 response, including its status and message, instead of a `ValidationException` thrown locally before the request.


## [v0.1.1](https://github.com/runapi-ai/openai-transcription-php/releases/tag/v0.1.1) - 2026-09-04

### Changed
- Preserve JSON, text, SRT, and VTT terminal transcription results after accepted Task polling.


## [v0.1.0](https://github.com/runapi-ai/openai-transcription-php/releases/tag/v0.1.0) - 2026-08-07

### Added
- Add synchronous multipart audio transcription with JSON, text, and subtitle response preservation.
