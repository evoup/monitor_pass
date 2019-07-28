<template>
  <el-table
    :data="dataList2"
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
      <el-table-column label="数目" prop="num" width="180" />
    </el-table-column>

  </el-table>
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
