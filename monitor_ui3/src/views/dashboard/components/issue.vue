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
      <el-table-column label="问题事件">
        <el-table-column
          prop="host"
          label="主机"
          min-width="16%"
        />
        <el-table-column label="问题" prop="issue" min-width="16%" />
        <el-table-column label="时长" prop="age" min-width="20%" />
        <el-table-column label="最后改动" prop="last_change" min-width="28%" />
        <el-table-column label="已确认" prop="ack" min-width="20%" />
      </el-table-column>

    </el-table>
  </div>

</template>

<script>
export default {
  name: 'Issue',
  data() {
    return {
      dataList: [
        {
          host: 'serve',
          issue: '主机',
          last_change: '2019-07-29 12:01:03',
          age: 180,
          ack: 1
        },
        {
          host: 'serve',
          issue: '主机',
          last_change: '2019-07-29 12:11:03',
          age: 180,
          ack: 1
        },
        {
          host: 'serve',
          issue: '主机',
          last_change: '2019-07-29 12:16:03',
          age: 180,
          ack: 1
        }
      ]
    }
  },
  methods: {
    myHeaderStyle({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 1) {
        return 'background:#CED7DF;color:#1f1f1f;text-align:left;font-weight:500;font-size:10px;'
      }
      return 'background:-webkit-gradient(linear, left top, left bottom, from(#4e6ea7), to(#698CB8));;color:#fff;text-align:left;font-weight:bold;font-size:10px;'
    }
  }
}
</script>

<style scoped>
  .my /deep/ .el-table .cell {
    line-height: 14px;
    font-size:12px;
  }
</style>
