<template>
  <el-table
    :data="dataList"
    :header-cell-style="myHeaderStyle"
    stripe
    border
    tooltip-effect="dark"
    style="width: 100%"
  >
    <el-table-column align="center" label="主机">
      <el-table-column
        prop="name"
        label="项目"
        min-width="50%"
        align="center"
      />
      <el-table-column label="数目" min-width="50%">
        <template slot-scope="scope">
          <a v-if="scope.row.num==0" :href="scope.row.num" target="_blank">{{ scope.row.num }}</a>
          <a v-if="scope.row.num>0 && scope.row.name=='宕机'" class="warn" @click="jumpServerList">{{ scope.row.num }}</a>
          <a v-if="scope.row.num>0 && scope.row.name=='在线'" :href="scope.row.num" target="_blank" class="online">{{ scope.row.num }}</a>
          <a v-if="scope.row.num>0 && scope.row.name=='未监控'" :href="scope.row.num" target="_blank">{{ scope.row.num }}</a>
        </template>
      </el-table-column>
    </el-table-column>

  </el-table>
</template>

<script>
export default {
  name: 'Host',
  data() {
    return {
      dataList: [
        {
          name: '宕机',
          num: 3
        }, {
          name: '在线',
          num: 31
        }, {
          name: '未监控',
          num: 31
        }
      ]
    }
  },
  methods: {
    jumpServerList() {
      this.$router.push({ path: '/server_list' })
    },
    myHeaderStyle({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 1) {
        return { display: 'none' }
      }
      return 'background:#486586;color:#fff;text-align:center;font-weight:500;font-size:10px;'
    }
  }
}
</script>

<style scoped>
.warn {
  color: red;
}
.online {
  color: #00AA00;
}
</style>
