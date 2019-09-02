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
      <el-table-column label="主机">
        <el-table-column
          prop="name"
          label="项目"
          min-width="50%"
        />
        <el-table-column label="数目" min-width="50%">
          <template slot-scope="scope">
            <a v-if="scope.row.num===0" :href="scope.row.num" target="_blank">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name==='宕机'" class="warn" @click="jumpServerList(1)">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name==='在线'" target="_blank" class="online" @click="jumpServerList(2)">{{ scope.row.num }}</a>
            <a v-if="scope.row.num>0 && scope.row.name==='未监控'" target="_blank" @click="jumpServerList(3)">{{ scope.row.num }}</a>
          </template>
        </el-table-column>
      </el-table-column>

    </el-table>
  </div>

</template>

<script>
import { dashboard_server_list } from '../../../api/dashboard'

export default {
  name: 'Host',
  data() {
    return {
      dataList: [
        {
          name: '宕机',
          num: 0
        }, {
          name: '在线',
          num: 0
        }, {
          name: '未监控',
          num: 0
        }
      ]
    }
  },
  created() {
    this.fetchData()
  },
  methods: {
    fetchData() {
      dashboard_server_list().then(response => {
        for (let i in response.data.items) {
          if (response.data.items[i].status === 0) {
            this.dataList[0].num++
          } else if (response.data.items[i].status === 1) {
            this.dataList[1].num++
          } else if (response.data.items[i].status === 2) {
            this.dataList[2].num++
          }
        }
      })
    },
    jumpServerList(type) {
      this.$router.push({ path: '/server_list?type=' + type })
    },
    myHeaderStyle({ row, column, rowIndex, columnIndex }) {
      if (rowIndex === 1) {
        return { display: 'none' }
      }
      return 'background:-webkit-gradient(linear, left top, left bottom, from(#4e6ea7), to(#698CB8));color:#fff;text-align:left;font-weight:bold;font-size:10px;'
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
.my /deep/ .el-table .cell {
  line-height: 14px;
  font-size:12px;
}
</style>
