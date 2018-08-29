import * as mongoose from 'mongoose';

import {getActiveSmps} from '../../helpers/smp';
import {updateElasticIndexOnSave} from '../../helpers/elastic';

import createIndexes from '../../config/indexDb';
import {IBuyRate} from "../buyRate/buyRate.model";

const Schema = mongoose.Schema;

const smpEnumToAttr: {[key:string]: string} = {
  saveya: 'saveYa',
  cardcash: 'cardCash',
  cardpool: 'cardPool',
  giftcardzen: 'giftcardZen',
  cardquiry: 'cardQuiry',
  zeek: 'zeek'
};

export enum SmpType {
  PHYSICAL = 'physical',
  ELECTRONIC = 'electronic',
  disabled = 'DISABLED'
}

export interface ISmpMaxMin {
  [key: string]: {
    max: string,
    min: string
  }
}

export interface IRetailer extends mongoose.Document {
  name: string;
  gsId: string;
  aiId: string;
  biActive: boolean;
  image: {
    url: string;
    original: string;
    type: string;
  };
  imageUrl: string;
  imageOriginal: string;
  imageType: string;
  buyRate: number;
  sellRates: {
    saveYa: number;
    cardCash: number;
    cardPool: number;
    giftcardZen: number;
    cardQuiry: number;
    zeek: number;
    best: number;
    sellTo: string;
  };
  sellRatesMerch: {
    saveYa: number;
    cardCash: number;
    cardpool: number;
    giftcardZen: number;
    cardQuiry: number;
    zeek: number;
  };
  smpSpelling: {
    saveYa: string;
    cardCash: string;
    cardPool: string;
    giftcardZen: string;
    cardQuiry: string;
    zeek: string;
  };
  smpMaxMin: {
    saveYa: {
      max: number;
      min: number;
    };
    cardCash: {
      max: number;
      min: number;
    };
    cardPool: {
      max: number;
      min: number;
    };
    giftcardZen: {
      max: number;
      min: number;
    };
    cardQuiry: {
      max: number;
      min: number;
    };
    zeek: {
      max: number;
      min: number;
    }
  };
  smpMaxMinMerch: {
    saveYa: {
      max: number;
      min: number;
    };
    cardCash: {
      max: number;
      min: number;
    };
    cardPool: {
      max: number;
      min: number;
    };
    giftcardZen: {
      max: number;
      min: number;
    };
    cardQuiry: {
      max: number;
      min: number;
    };
    zeek: {
      max: number;
      min: number;
    };
  };
  smpType: {
    saveYa: SmpType;
    cardCash: SmpType;
    cardPool: SmpType;
    giftcardZen: SmpType;
    cardQuiry: SmpType;
    zeek: SmpType;
  };
  smpTypeMerch: {
    saveYa: SmpType;
    cardCash: SmpType;
    cardPool: SmpType;
    giftcardZen: SmpType;
    cardQuiry: SmpType;
    zeek: SmpType;
  };
  apiId: {
    saveYa: string;
    cardCash: string;
    cardPool: string;
    giftcardZen: string;
  };
  verification: {
    url: string;
    phone: string;
  };
  buyRateRelations: mongoose.Types.ObjectId[] & IBuyRate[];
  original: mongoose.Types.ObjectId & IRetailer;
  pinRequired: boolean;
  numberRegex: string;
  pinRegex: string;

  getSellRatesMerch: () => object;
  getSmpMaxMinMerch: () => object;
  getSmpTypeMerch: () => object;
  populateMerchValues: (parent: object) => object;
  getSellRates: () => object;
  getSmpMaxMin: () => ISmpMaxMin;
  getSmpType: () => object;
  getSmpSpelling: () => object;

  // This is only used to compare changes that might need to be synced with ES
  _originals: {name: string};
}

export interface IRetailerModel extends mongoose.Model<IRetailer> { }

const RetailerSchema = new Schema({
  // Company name
  name: {
    type: String,
    required: true,
    es_fields: {keyword: {type: 'string', index: 'not_analyzed'}}
  },
  // GiftSquirrel ID
  gsId: String,
  // Addtoit ID
  aiId: String,
  // BI active
  biActive: Boolean,
  image: {
    url: String,
    original: String,
    type: String
  },
  imageUrl: String,
  imageOriginal: String,
  imageType: String,
  buyRate: Number,
  sellRates: {
    saveYa: Number,
    cardCash: Number,
    cardPool: Number,
    giftcardZen: Number,
    cardQuiry: Number,
    zeek: Number,
    best: Number,
    sellTo: String
  },
  sellRatesMerch: {
    saveYa: Number,
    cardCash: Number,
    cardPool: Number,
    giftcardZen: Number,
    cardQuiry: Number,
    zeek: Number,
  },
  // Spelling
  smpSpelling: {
    saveYa: {type: String},
    cardCash: {type: String},
    cardPool: {type: String},
    giftcardZen: {type: String},
    cardQuiry: {type: String},
    zeek: {type: String},
  },
  // Max/min for retailers by SMP
  smpMaxMin: {
    saveYa: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardCash: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardPool: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    giftcardZen: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardQuiry: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    zeek: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    }
  },
  smpMaxMinMerch: {
    saveYa: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardCash: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardPool: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    giftcardZen: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    cardQuiry: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    },
    zeek: {
      max: {type: Number, min: 0},
      min: {type: Number, min: 0}
    }
  },
  // SMP type (electronic, physical, disabled)
  smpType: {
    saveYa: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardCash: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardPool: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    giftcardZen: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardQuiry: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    zeek: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
  },
  smpTypeMerch: {
    saveYa: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardCash: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardPool: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    giftcardZen: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    cardQuiry: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
    zeek: {type: String, enum: ['physical', 'electronic', 'disabled'], get: convertToLowerCase, set: convertToLowerCase},
  },
  // API IDs (the ID for SMP APIs)
  apiId: {
    saveYa: String,
    cardCash: String,
    cardPool: String,
    giftcardZen: String
  },
  // Verification info
  verification: {
    url: String,
    phone: String
  },
  buyRateRelations: [{type: Schema.Types.ObjectId, ref: 'BuyRate'}],
  original: {type: Schema.Types.ObjectId, ref: 'Retailer'},
  pinRequired: {type: Boolean, default: false},
  numberRegex: {type: String},
  pinRegex: {type: String}
});

// Indexes
const indexes = [
  [{name: 1}],
  [{gsId: 1}],
  [{aiId: 1}],
];
createIndexes(RetailerSchema, indexes);

/**
 * Validations
 */

// Validate empty name
RetailerSchema
  .path('name')
  .validate(function (name: string) {
    return name.length;
  }, 'Retailer name cannot be blank');

// Validate duplicate names
RetailerSchema
  .path('name')
  .validate({isAsync: true, validator: function(name: string, cb: Function) {
    this.constructor.findOne({name}, (err: Error, retailer: IRetailer) => {
      if (err) {
        throw err;
      }
      if (retailer) {
        if (this.id === retailer.id) {
          return cb(true);
        }
        return cb(false);
      }
      return cb(true);
    });
  }, message: 'Retailer name is already taken'});

RetailerSchema.methods.getSellRatesMerch = function () {
  const sellRates = this.sellRatesMerch || {};

  getActiveSmps().forEach((smp: string) => {
    if (typeof sellRates[smpEnumToAttr[smp]] === 'undefined') {
      sellRates[smpEnumToAttr[smp]] = this.sellRates[smpEnumToAttr[smp]];
    }
  });

  return sellRates;
};

RetailerSchema.methods.getSmpMaxMinMerch = function () {
  const maxMin = this.smpMaxMinMerch || {};

  getActiveSmps().forEach((smp: string) => {
    if (typeof maxMin[smpEnumToAttr[smp]] === 'undefined') {
      maxMin[smpEnumToAttr[smp]] = {};
    }

    ['max', 'min'].forEach(k => {
      if (typeof maxMin[smpEnumToAttr[smp]][k] === 'undefined') {
        maxMin[smpEnumToAttr[smp]][k] = this.smpMaxMin[smpEnumToAttr[smp]][k];
      }
    });
  });

  return maxMin;
};

RetailerSchema.methods.getSmpTypeMerch = function () {
  const types = this.smpTypeMerch || {};

  getActiveSmps().forEach((smp: string) => {
    if (typeof types[smpEnumToAttr[smp]] === 'undefined') {
      types[smpEnumToAttr[smp]] = this.smpType[smpEnumToAttr[smp]];
    }
  });

  return types;
};

/**
 * Populate a retailer attached to a card or inventory with merch values if necessary
 * @param {Object} parent Card or inventory record
 */
RetailerSchema.methods.populateMerchValues = (parent: any) => {
  let retailer = parent.retailer;
  if (parent.merchandise) {
    // Assign merch values, assume default if not set
    const merchRates = retailer.getSellRatesMerch();
    const merchMaxMin = retailer.getSmpMaxMinMerch();
    const merchType = retailer.getSmpTypeMerch();
    retailer = retailer.toObject();
    Object.assign(retailer.sellRates, merchRates.toObject());
    Object.assign(retailer.smpMaxMin, merchMaxMin.toObject());
    Object.assign(retailer.smpType, merchType.toObject());
    return retailer;
  } else {
    // Convert to object if necessary
    if (retailer.constructor.name === 'model') {
      return retailer.toObject();
    } else {
      return retailer;
    }
  }
};

RetailerSchema.methods.getSellRates = function () {
  getActiveSmps().forEach((smp: string) => {
    if (typeof this.sellRates[smpEnumToAttr[smp]] === 'undefined') {
      this.sellRates[smpEnumToAttr[smp]] = 0;
    }
  });

  return this.sellRates;
};

RetailerSchema.methods.getSmpMaxMin = function () {
  getActiveSmps().forEach((smp: string) => {
    if (typeof this.smpMaxMin[smpEnumToAttr[smp]] === 'undefined') {
      this.smpMaxMin[smpEnumToAttr[smp]] = {max: null, min: 0};
    }

    ['max', 'min'].forEach(k => {
      if (typeof this.smpMaxMin[smpEnumToAttr[smp]][k] === 'undefined') {
        this.smpMaxMin[smpEnumToAttr[smp]][k] = k === 'min' ? 0 : null;
      }
    })
  });

  return this.smpMaxMin;
};

RetailerSchema.methods.getSmpType = function () {
  getActiveSmps().forEach((smp: string) => {
    if (typeof this.smpType[smpEnumToAttr[smp]] === 'undefined') {
      this.smpType[smpEnumToAttr[smp]] = 'disabled';
    }
  });

  return this.smpType;
};

RetailerSchema.methods.getSmpSpelling = function () {
  getActiveSmps().forEach((smp: string)=> {
    if (typeof this.smpSpelling[smpEnumToAttr[smp]] === 'undefined' || this.smpSpelling[smpEnumToAttr[smp]] === '') {
      this.smpSpelling[smpEnumToAttr[smp]] = this.name;
    }
  });

  return this.smpSpelling;
};

function convertToLowerCase(whatever?: string) {
  if (whatever) {
    return whatever.toLowerCase();
  }
}

RetailerSchema.set('toJSON', {getters: true});
RetailerSchema.set('toObject', {getters: true});

RetailerSchema.path('name').set(function (newVal: string) {
  this._originals = {name: this.name};
  return newVal;
});

RetailerSchema.pre('save', function (next) {
  this._originals = this._originals || {};
  next();
});

updateElasticIndexOnSave(RetailerSchema, (doc: IRetailer) => {
  if (typeof doc._originals.name !== 'string' || doc.name === doc._originals.name) {
    return null;
  }

  return {
    body: {
      query: {
        bool: {
          must: [
            {
              match: {
                "retailer._id": doc._id
              }
            }
          ]
        }
      },
      script: {
        inline: "ctx._source['retailer']['name'] = params.newName",
        params: {
          newName: doc.name
        }
      }
    },
    index: "inventories",
    type: "inventory"
  };
});

export const Retailer: IRetailerModel = mongoose.model<IRetailer, IRetailerModel>('Retailer', RetailerSchema);

export default Retailer;
