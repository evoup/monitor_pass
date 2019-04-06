<template>
  <div class="app-container">
    d
  </div>
</template>

<script>
import { server_group_list } from '@/api/server'
export default {
  data() {
    return {
      form: {
        client: '',
        name: '',
        jmx: '',
        snmp: '',
        idc: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: ''
      }
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      server_group_list(Object.assign(this.pageHelp, this.sortHelp)).then(response => {
      })
    }
  }
}
</script>

<style scoped>

</style>
