<template>
  <div class="app-container">
    <el-form ref="form">
      <el-form-item label="服务器组">
        <el-row>
          <el-col :span="18">
            <el-select
              v-model="serverGroupSelectModel"
              placeholder="请选择服务器组"
              @change="fetchData"
            >
              <el-option
                v-for="item in serverGroups"
                :key="item.id"
                :label="item.name"
                :aria-selected="true"
                :value="item.id"
              />
            </el-select>
          </el-col>
          <el-col :span="4" align="right">
            <div class="grid-content">
              <el-button type="primary" @click="jumpAddServer()"><i class="el-icon-plus el-icon--right" />添加服务器</el-button>
            </div>
          </el-col>
        </el-row>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
export default {
  name: 'EventList'
}
</script>

<style scoped>

</style>
