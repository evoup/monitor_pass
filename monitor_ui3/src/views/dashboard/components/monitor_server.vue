<template>
  <div class="my">
    <el-table
      :data="dataList"
      :header-cell-style="myHeaderStyle"
      stripe
      border
      tooltip-effect="dark"
      style="width: 100%"
    >
      <el-table-column align="center" label="监控服务器状态">
        <el-table-column
          prop="name"
          label="项目"
          min-width="50%"
          align="center"
        />
        <el-table-column label="信息" prop="info" min-width="50%" />
      </el-table-column>

    </el-table>
  </div>
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
    myHeaderStyle({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 1) {
        return 'display:none;'
      }
      return 'background:-webkit-gradient(linear, left top, left bottom, from(#4e6ea7), to(#698CB8));color:#fff;text-align:center;font-weight:500;font-size:10px;'
    }
  }
}
</script>

<style scoped>
  .my /deep/ .el-table .cell {
    line-height: 12px;
    font-size:12px;
  }
</style>
