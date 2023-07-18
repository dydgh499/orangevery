<script setup lang="ts">
import type { ThemeSwitcherTheme } from '@layouts/types'

const themes: ThemeSwitcherTheme[] = [
  {
    name: 'light',
    icon: 'line-md:moon-loop',
  },
  {
    name: 'dark',
    icon: 'line-md:moon-alt-to-sunny-outline-loop-transition',
  },
]
</script>

<template>
  <ThemeSwitcher :themes="themes" />
</template>
