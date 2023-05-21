import { axios } from '@axios'
import { defineStore } from 'pinia'
import type { ProjectParams } from './types'

export const useProjectStore = defineStore('ProjectStore', {
  actions: {
    // 👉 Fetch all project
    fetchProjects(params: ProjectParams) {
      return axios.get('/dashboard/analytics/projects', { params })
    },
  },
})
