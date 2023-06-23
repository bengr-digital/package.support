<?php

namespace Bengr\Support\Models\Concerns;

use Spatie\Translatable\Events\TranslationHasBeenSetEvent;
use Spatie\Translatable\HasTranslations as TranslatableHasTranslations;
use Illuminate\Support\Str;
use Spatie\Translatable\Translatable;

trait HasTranslations
{
    use TranslatableHasTranslations;

    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        $translations = $this->getTranslations($key);

        $oldValue = $translations[$locale] ?? '';

        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$key];
        } else if ($this->hasAttributeSetMutator($key)) {
            $this->setAttributeMarkedMutatedAttributeValue($key, $value);

            $value = $this->attributes[$key];
        }

        $translations[$locale] = $value;

        $this->attributes[$key] = $this->asJson($translations);

        event(new TranslationHasBeenSetEvent($this, $key, $locale, $oldValue, $value));

        return $this;
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        $normalizedLocale = $this->normalizeLocale($key, $locale, $useFallbackLocale);

        $isKeyMissingFromLocale = ($locale !== $normalizedLocale);

        $translations = $this->getTranslations($key);

        $translation = $translations[$normalizedLocale] ?? '';

        $translatableConfig = app(Translatable::class);

        if ($isKeyMissingFromLocale && $translatableConfig->missingKeyCallback) {
            try {
                $callbackReturnValue = (app(Translatable::class)->missingKeyCallback)($this, $key, $locale, $translation, $normalizedLocale);
                if (is_string($callbackReturnValue)) {
                    $translation = $callbackReturnValue;
                }
            } catch (\Exception) {
                //prevent the fallback to crash
            }
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        } else if ($this->hasAttributeMutator($key)) {
            return $this->mutateAttributeMarkedAttribute($key, $translation);
        }

        return $translation;
    }
}
