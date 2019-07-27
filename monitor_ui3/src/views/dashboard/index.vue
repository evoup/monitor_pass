<template>
  <div class="dashboard-container">
    <el-row>
      <el-col :span="8">
        <el-table
          :data="dataList"
          :header-cell-style="discountHeaderStyle1"
          stripe
          border
          tooltip-effect="dark"
          style="width: 100%"
        >
          <el-table-column align="center" label="主机">
            <el-table-column
              prop="name"
              label="项目"
              width="150"
              align="center"
            />
            <el-table-column label="数目" prop="num" width="150" />
          </el-table-column>

        </el-table>
      </el-col>
      <el-col :span="8" />
      <el-col :span="8" />
    </el-row>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'Dashboard',
  data() {
    return {
      dataList: [
        {
          date: '2016-05-03',
          name: '宕机',
          num: 3
        }, {
          date: '2016-05-03',
          name: '在线',
          num: 31
        }, {
          date: '2016-05-03',
          name: '未监控',
          num: 31
        }
      ]
    }
  },
  computed: {
    ...mapGetters(['name', 'roles'])
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

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard {
  &-container {
    margin: 30px;
  }
  &-text {
    font-size: 30px;
    line-height: 46px;
  }
}
</style>
