# Podcast Enhancer

Improve your podcasts with summaries, chapters, and transcripts.

## Self-hosting

This is a standard Laravel application, you can learn more about deploying Laravel applications in [the official docs](https://laravel.com/docs/deployment). It also uses the Laravel AI SDK, so you'll need to set the following env variables:

- `AI_DEFAULT_PROVIDER` (Used for summaries and chapters generation)
- `AI_DEFAULT_FAILOVER_PROVIDER` (Used for summaries and chapters generation when the default provider fails)
- `AI_DEFAULT_PROVIDER_FOR_TRANSCRIPTION`
- `MISTRAL_API_KEY`, `GEMINI_API_KEY`, etc. (depending on the providers you've configured)
