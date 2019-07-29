<template>
  <div class="my">
    <el-table
      :data="dataList2"
      :header-cell-style="myHeaderStyle"
      stripe
      border
      tooltip-effect="dark"
      style="width: 100%"
    >
      <el-table-column align="center" label="事件">
        <el-table-column
          prop="name"
          label="项目"
          min-width="50%"
          align="center"
        />
        <el-table-column label="数目" min-width="50%">
          <template slot-scope="scope">
            <a v-if="scope.row.num==0" :href="scope.row.num" target="_blank">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name=='警告'" :href="scope.row.num" target="_blank" class="warn">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name=='严重警告'" :href="scope.row.num" target="_blank" class="warn">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name=='灾难警告'" :href="scope.row.num" target="_blank" class="warn">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name=='信息'" :href="scope.row.num" target="_blank">{{ scope.row.num }}</a>
          </template>
        </el-table-column>
      </el-table-column>

    </el-table>
  </div>

</template>

<script>
export default {
  name: 'Event',
  data() {
    return {
      dataList2: [
        {
          name: '信息',
          num: 3
        }, {
          name: '警告',
          num: 31
        }, {
          name: '严重警告',
          num: 31
        }, {
          name: '灾难警告',
          num: 31
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
  .warn {
    color: red;
  }
  .my /deep/ .el-table .cell {
    line-height: 12px;
    font-size:12px;
  }
</style>
