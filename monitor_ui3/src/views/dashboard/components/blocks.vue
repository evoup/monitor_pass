<template>
  <div class="fluid container">
    <el-row>
      <el-col :span="8">
        <draggable v-model="list" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list" :key="element.order" class="list-group-item">
            <component :is="element.comp_name"/>
          </li>
          <div v-if="list.length==0" @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>

        </draggable>
      </el-col>
      <el-col :span="8">
        <draggable v-model="list2" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list2" :key="element.order" class="list-group-item">
            <component :is="element.comp_name"/>
          </li>
          <div v-if="list2.length==0" @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>
        </draggable>
      </el-col>
      <el-col :span="8">
        <draggable v-model="list3" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list3" :key="element.order" class="list-group-item">
            <component :is="element.comp_name"/>
          </li>
          <div v-if="list3.length==0" @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>
        </draggable>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import Host from './host'
import Event from './event'
import MonitorServer from './monitor_server'
export default {
  name: 'Blocks',
  components: {
    draggable,
    Host,
    Event,
    MonitorServer
  },
  data() {
    return {
      list: [{ name: '看板列1', order: 0, fixed: false, comp_name: 'Host' }],
      list2: [{ name: '看板列2', order: 1, fixed: false, comp_name: 'Event' }],
      list3: [{ name: '看板列3', order: 2, fixed: false, comp_name: 'MonitorServer' }],
      isDragging: false,
      delayedDragging: false
    }
  },
  computed: {
    dragOptions() {
      return {
        animation: 0,
        group: 'description',
        disabled: false,
        ghostClass: 'ghost'
      }
    }
  },
  watch: {
    isDragging(newValue) {
      if (newValue) {
        this.delayedDragging = true
        return
      }
      this.$nextTick(() => {
        this.delayedDragging = false
      })
    }
  },
  methods: {
    onMove({ relatedContext, draggedContext }) {
      const relatedElement = relatedContext.element
      const draggedElement = draggedContext.element
      return (
        (!relatedElement || !relatedElement.fixed) && !draggedElement.fixed
      )
    }
  }
}
</script>

<style>
  .flip-list-move {
    transition: transform 0.5s;
  }
  .no-move {
    transition: transform 0s;
  }
  .ghost {
    opacity: 0.5;
    background: #c8ebfb;
  }
  .list-group {
    min-height: 20px;
    padding-left:0;
  }
  .list-group-item {
    cursor: move;
    list-style: none;
  }
  .list-group-item i {
    cursor: pointer;
  }
</style>
