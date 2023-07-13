<script setup lang="ts">
import type { ThemeSwitcherTheme } from '@layouts/types'

const themes: ThemeSwitcherTheme[] = [
  {
    name: 'light',
    icon: 'line-md:moon-alt-to-sunny-outline-loop-transition',
  },
  {
    name: 'dark',
    icon: 'line-md:moon-loop',
  },
]
</script>

<template>
  <ThemeSwitcher :themes="themes" />
</template>
