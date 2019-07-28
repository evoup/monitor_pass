<template>
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
        width="180"
        align="center"
      />
      <el-table-column label="数目" prop="num" width="180" />
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
