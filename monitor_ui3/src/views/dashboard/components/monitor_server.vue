<template>
  <el-table
    :data="dataList"
    :header-cell-style="discountHeaderStyle1"
    stripe
    border
    tooltip-effect="dark"
    style="width: 100%"
  >
    <el-table-column align="center" label="事件">
      <el-table-column
        prop="name"
        label="项目"
        width="180"
        align="center"
      />
      <el-table-column label="信息" prop="info" width="180" />
    </el-table-column>

  </el-table>
</template>

<script>
export default {
  name: 'MonitorServer',
  data() {
    return {
      dataList: [
        {
          name: '监控服务端程序',
          info: '2个节点在线     0个节点宕机'
        }, {
          name: 'OpenTSDB',
          info: '2个节点在线     0个节点宕机'
        }, {
          name: '监控状态',
          info: '良好'
        }
      ]
    }
  },
  methods: {
    discountHeaderStyle1({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 1) {
        return { display: 'none' }
      }
    }
  }
}
</script>

<style scoped>

</style>
