<template>
  <el-table
    :data="tableData"
    stripe
    style="width: 100%">
    <el-table-column
      label="主机名"
      prop="name"
      width="180" />
    <el-table-column
      label="IP"
      prop="ip"
      width="180" />
    <el-table-column
      label="收集节点"
      prop="data_collector"
      width="180" />
    <el-table-column
      label="更新时间"
      prop="date"
      width="180" />
    <el-table-column
      label="状态"
      prop="status"
      width="180" />
    <el-table-column
      label="机房"
      prop="address" />
  </el-table>
</template>

<script>
import { server_list } from '@/api/server'
export default {
  data() {
    return {
      tableData: [{
        name: 'server1',
        ip: '222.73.254.111',
        data_collector: 'data_collector0',
        date: '2016-05-02',
        status: '在线',
        address: '花木机房'
      }, {
        name: 'server2',
        ip: '222.73.254.111',
        data_collector: 'data_collector0',
        date: '2016-05-04',
        status: '在线',
        address: '花木机房'
      }, {
        name: 'server3',
        ip: '222.73.254.111',
        data_collector: 'data_collector0',
        date: '2016-05-01',
        status: '在线',
        address: '花木机房'
      }, {
        name: 'server4',
        ip: '222.73.254.111',
        data_collector: 'data_collector0',
        date: '2016-05-03',
        status: '在线',
        address: '花木机房'
      }]
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      this.listLoading = true
      server_list(this.listQuery).then(response => {
        this.list = response.data.items
        this.listLoading = false
      })
    }
  }
}
</script>
