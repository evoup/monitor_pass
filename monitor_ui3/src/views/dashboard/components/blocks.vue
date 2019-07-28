<template>
  <div class="fluid container">
    <el-row>
      <el-col :span="8">
        <draggable v-model="list" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list" :key="element.order" class="list-group-item">
            {{ element.name }}
            <span class="badge">{{ element.order }}</span>
            <component :is="element.comp_name"/>
          </li>
          <div @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>

        </draggable>
      </el-col>
      <el-col :span="8">
        <draggable v-model="list2" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list2" :key="element.order" class="list-group-item">
            {{ element.name }}
            <span class="badge">{{ element.order }}</span>
            <component :is="element.comp_name"/>
          </li>
          <div @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>
        </draggable>
      </el-col>
      <el-col :span="8">
        <draggable v-model="list3" v-bind="dragOptions" :move="onMove" class="list-group" tag="ul" @start="isDragging=true" @end="isDragging=false">
          <li v-for="element in list3" :key="element.order" class="list-group-item">
            {{ element.name }}
            <span class="badge">{{ element.order }}</span>
            <component :is="element.comp_name"/>
          </li>
          <div @start="isDragging=false" @end="isDragging=false"><a>添加图表看板</a></div>
        </draggable>
      </el-col>
    </el-row>

    <div class="list-group col-md-3">
      <pre>{{ listString }}</pre>
    </div>
    <div class="list-group col-md-3">
      <pre>{{ list2String }}</pre>
    </div>
    <div class="list-group col-md-3">
      <pre>{{ list3String }}</pre>
    </div>
  </div>
</template>

<script>
import draggable from 'vuedraggable'
import Host from './host'
import Event from './event'
export default {
  name: 'Blocks',
  components: {
    draggable,
    Host,
    Event
  },
  data() {
    return {
      list: [{ name: 'xx', order: 0, fixed: false, comp_name: 'Host' }],
      list2: [{ name: 'xx', order: 1, fixed: false, comp_name: 'Event' }],
      list3: [{ name: 'xx', order: 2, fixed: false, comp_name: 'Host' }],
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
    },
    listString() {
      return JSON.stringify(this.list, null, 2)
    },
    list2String() {
      return JSON.stringify(this.list2, null, 2)
    },
    list3String() {
      return JSON.stringify(this.list3, null, 2)
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
  }
  .list-group-item {
    cursor: move;
  }
  .list-group-item i {
    cursor: pointer;
  }
</style>
