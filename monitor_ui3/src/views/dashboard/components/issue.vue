<template>
  <div class="my">
    <el-table
      :data="dataList"
      :header-cell-style="myHeaderStyle"
      :row-style="tdStyle"
      :cell-style="tdStyle"
      stripe
      border
      tooltip-effect="dark"
      style="width: 100%"
    >
      <el-table-column label="问题事件">
        <el-table-column
          prop="host"
          label="主机"
          min-width="15%"
        />
        <el-table-column label="问题" prop="issue" min-width="36%" />
        <el-table-column label="时长" prop="age" min-width="15%" />
        <el-table-column label="最后改动" prop="last_change" min-width="24%" />
        <el-table-column label="已确认" prop="ack" min-width="10%">
          <template slot-scope="scope">
            <p v-if="scope.row.ack ===1" style="color: #00AA00">
              是
            </p>
            <p v-else-if="scope.row.ack===0">
              否
            </p>
          </template>
        </el-table-column>
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
          host: 'adserver01',
          level: 1,
          issue: 'Zabbix agent on 192.168.2.197 is unreachable for 5 minutes',
          last_change: '2019-07-29 12:01:03',
          age: 180,
          ack: 1
        },
        {
          host: 'scribeHm02',
          level: 2,
          issue: 'Zabbix agent on 192.168.2.197 is unreachable for 5 minutes',
          last_change: '2019-07-29 12:11:03',
          age: 180,
          ack: 0
        },
        {
          host: 'haproxyZj01',
          level: 3,
          issue: 'Zabbix agent on 192.168.2.197 is unreachable for 5 minutes',
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
    },
    tdStyle({ row, column, rowIndex, columnIndex }) {
      if (columnIndex === 1) {
        if (row.level === 1) {
          return 'background:#d6f6fd;color:#1f1f1f;text-align:left;font-weight:500;font-size:10px;'
        }
        if (row.level === 2) {
          return 'background:#fff2a5;color:#1f1f1f;text-align:left;font-weight:500;font-size:10px;'
        }
        if (row.level === 3) {
          return 'background:#feb689;color:#1f1f1f;text-align:left;font-weight:500;font-size:10px;'
        }
        return 'background:#CED7DF;color:#1f1f1f;text-align:left;font-weight:500;font-size:10px;'
      }
      return 'text-align:left;font-size:10px;'
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
